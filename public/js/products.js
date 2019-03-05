/**
 * Products table class
 * @constructor
 */
window.Products = function(){
    this.table = $("#product_table tbody");
    this.link = '/products';
    this.sections = [];
    $(document).ready($.proxy(this.init, this));
};

window.Products.prototype = Object.create(Table.prototype);

/**
 * make one section row
 * @param product
 * @returns {jQuery|HTMLElement}
 */
window.Products.prototype.makeRow = function (product) {
    var self = this;
    var tr = $("<tr/>");
    tr.append($('<td>').html('<input type="checkbox" name="ids[]" value="'+product.id+'" class="checkbox-product"/>'));
    tr.append($('<td>').html(product.id));
    tr.append($('<td>').addClass('name-field')
        .data('value', product.name)
        .html(product.name));
    tr.append($('<td>')
        .addClass('is_active-field')
        .data('value', product.is_active)
        .html(product.is_active));
    tr.append($('<td>')
        .addClass('section-field')
        .data('value', product.section ? product.section.id : '')
        .html(product.section ? product.section.name : ''));
    tr.append($('<td/>').append(self.makeReviews(product.reviews)));
    tr.append(
        $('<td/>')
            .append(self.makeButton(product.id, 'Reviews', 'btn-primary reviews-product  glyphicon-comment'))
            .append(self.makeButton(product.id, 'Edit', 'btn-primary edit-product  glyphicon-pencil'))
            .append(self.makeButton(product.id, 'Delete', 'btn-danger delete-product  glyphicon-trash'))
    );
    this.table.append(tr);
    return tr;
};

/**
 * make reviews block
 * @param reviews
 * @returns {*|jQuery}
 */
window.Products.prototype.makeReviews = function (reviews) {
    var block = $("<div/>").addClass('review-block');
    $.each(reviews, function(){
        var item = $("<div/>").addClass('review');
        item.append('<span class="review-author">'+this.author+'</span>' +
            '(<span class="review-date">'+this.date+'</span>): ' +
            '<span class="review-text">'+this.text+'</span>');
        block.append(item);
    });
    return block;
};

/**
 * make reviews form
 * @param reviews
 * @returns {*|jQuery}
 */
window.Products.prototype.makeReviewsForm = function (reviews) {
    var self = this;
    var block = $("<div/>").addClass('review-block');
    var table = $("<table/>").addClass('table');
    table.append('<tr><th>Author</th><th>Date</th><th>Text</th><th></th></tr>');
    if(reviews.length > 0) {
        $.each(reviews, function () {
            table.append(self.makeReviewsFormRow(this));
        });
    }else{
        //add empty review
        table.append(self.makeReviewsFormRow());
    }
    block.append($("<button/>").addClass('btn btn-primary add-review').attr('type', 'button').text('Add review'));
    block.append(table);
    return block;
};

/**
 * make review row for form
 * @param review
 * @returns {*|jQuery}
 */
window.Products.prototype.makeReviewsFormRow = function (review) {
    if(!review){    //new review
        review = {
            id: 0,
            author: '',
            date: '',
            text: ''
        };
    }
    var item = $("<tr/>").addClass('review');
    item.append($('<td/>').append('<input type="hidden" name="review[id][]" value="'+review.id+'">' +
        '<input class="form-control" type="text" name="review[author][]" value="'+review.author+'">'));
    item.append($('<td/>').append('<input class="form-control datepicker" type="text" name="review[date][]" value="'+review.date+'">'));
    item.append($('<td/>').append('<textarea class="form-control" name="review[text][]">'+review.text+'</textarea>'));
    item.append($('<td/>').append(
        $("<button/>").addClass('btn btn-danger btn-xs delete-review glyphicon glyphicon-trash').data('id', review.id).attr('type', 'button')
    ));

    return item;
};

window.Products.prototype.loadSections = function () {
    var self = this;
    self.ajaxSubmit({}, 'GET', '/sections?count=-1', 'json', function(data){
        self.sections = data.items;
        $(".for-select-section").each(function(){
            var currentValue = $(this).find('select').val();
            var currentName = $(this).find('select').attr('name');
            $(this).html(self.makeSectionSelect(currentValue).attr('name', currentName));
            $(this).find('select').selectpicker();
        });
    })
};

/**
 * click to reviews button
 * @param e
 */
window.Products.prototype.clickReviews = function (e) {
    e.preventDefault();
    var self = this;
    var element = e.target;
    var modal = $("#reviews-product");
    var id = $(element).data('id');
    modal.find('[name=product_id]').val(id);
    modal.find('.ajax-submit-modal').off('click');
    modal.find('.ajax-submit-modal').on('click', $.proxy(this.submitFormModal, self));
    this.ajaxSubmit({}, 'GET', '/products/'+id, 'json', function (data) {
        modal.find('.reviews').html(self.makeReviewsForm(data.reviews));
        self.initDatePicker();
        modal.modal('show');
    });
};

window.Products.prototype.clickDeleteReview = function (e) {
    e.preventDefault();
    var self = this;
    var element = e.target;
    var id = parseInt($(element).data('id'));
    if(id > 0) {
        self.ajaxSubmit({
            id: id
        }, 'DELETE', '/reviews', 'json', function (data) {
            $(element).closest('tr').detach();
            self.loadData();
        });
    }else{
        $(element).closest('tr').detach();
    }


};

window.Products.prototype.clickAddReview = function (e) {
    e.preventDefault();
    var element = e.target;
    var table = $(element).closest('.review-block').find('table');
    table.append(this.makeReviewsFormRow());
    this.initDatePicker();
};

window.Products.prototype.clickCreate = function (e) {
    e.preventDefault();
    var modal = $("#form-product");
    modal.find('form').attr('method', 'post');
    modal.find('.modal-title').text("Create product");
    modal.find('.ajax-submit-modal').text("Create");
    modal.find('[name=id]').val("");
    modal.find('[name=name]').val("");
    modal.find('[name=is_active]').prop('checked', false);
    modal.find('.for-select-section').html(this.makeSectionSelect().attr('name', 'section_id'));
    modal.find('[name=section_id]').val("").selectpicker();
    modal.find('.ajax-submit-modal').off('click');
    modal.find('.ajax-submit-modal').on('click', $.proxy(this.submitFormModal, this));
    this.loadSections();
    modal.modal('show');
};

window.Products.prototype.clickEdit = function (e) {
    e.preventDefault();
    var self = this;
    var element = e.target;
    var modal = $("#form-product");
    var id = $(element).data('id');
    modal.find('[name=id]').val(id);
    modal.find('.ajax-submit-modal').off('click');
    modal.find('.ajax-submit-modal').on('click', $.proxy(this.submitFormModal, self));
    this.ajaxSubmit({}, 'GET', '/products/'+id, 'json', function (data) {
        modal.find('form').attr('method', 'put');
        modal.find('.modal-title').text("Edit product");
        modal.find('.ajax-submit-modal').text("Update");
        modal.find('[name=name]').val(data.name);
        modal.find('[name=is_active]').prop('checked', data.is_active === 'yes');
        modal.find('.for-select-section').html(self.makeSectionSelect().attr('name', 'section_id'));
        modal.find('[name=section_id]').val(data.section_id).selectpicker('refresh');
        modal.modal('show');
    });
    this.loadSections();
};

window.Products.prototype.clickDelete = function (e) {
    e.preventDefault();
    var element = e.target;
    var id = $(element).data('id');
    this.confirmModal('Delete product', 'Are you sure you want to delete this product?', $.proxy(this.confirmDelete, this, id));
};

window.Products.prototype.confirmDelete = function (id) {
    var self = this;
    var modal = $("#confirm-modal");
    self.ajaxSubmit({
        id: id
    }, 'DELETE', '/products', 'json', function(data){
        self.loadData();
        modal.modal('hide');
    })
};

window.Products.prototype.makeIsActiveSelect = function (currentValue) {
    var select = $("<select/>");
    select.append("<option value='1' "+(currentValue ? "selected" : "")+">Yes</option>");
    select.append("<option value='0' "+(!currentValue ? "selected" : "")+">No</option>");
    return select;
};

window.Products.prototype.makeSectionSelect = function (currentValue) {
    var select = $("<select/>").addClass("selectpicker");
    select.append("<option value=''>no section</option>");
    $.each(this.sections, function(){
        select.append("<option value='"+this.id+"' "+(this.id === currentValue ? "selected" : "")+">"+this.name+"</option>");
    });
    return select;
};

window.Products.prototype.massDelete = function (e) {
    e.preventDefault();
    if($("#product_table .checkbox-product:checked").length > 0) {
        this.confirmModal(
            'Delete selected products',
            'Are you sure you want to delete this selected products?',
            $.proxy(this.massDeleteConfirm, this));
    }else{
        this.messageModal('Warning', 'No product selected');
    }
};

window.Products.prototype.massDeleteConfirm = function () {
    var ids = [];
    var self = this;
    $("#product_table .checkbox-product:checked").each(function(){
        ids.push($(this).val())
    });
    self.ajaxSubmit({
        ids: ids
    }, 'DELETE', '/products/mass-delete', 'json', function(data){
        self.loadData();
        $("#confirm-modal").modal('hide');
    })
};

window.Products.prototype.massEdit = function (e) {
    e.preventDefault();
    var self = this;
    var element = e.target;
    var checkbox = $("#product_table .checkbox-product:checked");
    if(checkbox.length > 0) {
        $("#product_table input[type=checkbox]").prop('readonly', true);
        $(element).removeClass('mass-edit-product').addClass('mass-update-product').text("Save");
        $(".mass-cancel-product").show();
        checkbox.each(function () {
            var tr = $(this).closest('tr');
            var nameField = tr.find('.name-field');
            var is_activeField = tr.find('.is_active-field');
            var sectionField = tr.find('.section-field');
            nameField.html("<input name='name[]' type='text' value='" + nameField.data('value') + "' />");
            is_activeField.html(
                self.makeIsActiveSelect(is_activeField.data('value') === 'yes').attr('name', 'is_active[]')
            );
            sectionField.html(
                $("<div/>").addClass('for-select-section').append(self.makeSectionSelect(sectionField.data('value')).attr('name', 'section_id[]')).html()
            );
            sectionField.find('select').selectpicker();
        });
        this.loadSections();
    }else{
        self.messageModal('Warning!', 'No product selected');
    }
};

window.Products.prototype.massUpdate = function (e) {
    e.preventDefault();
    var self = this;
    var form = $("#products_form");
    self.ajaxSubmit(form.serialize(), form.attr('method'), form.attr('action'), 'json', function(data){
        self.massEditClose();
    })
};

window.Products.prototype.massEditClose = function () {
    $(".mass-update-product").removeClass('mass-update-product').addClass('mass-edit-product').text("Edit selected");
    $(".mass-cancel-product").hide();
    $("#product_table input[type=checkbox]").prop('readonly', false).prop('checked', false);
    this.loadData();
};

window.Products.prototype.makePagination = function (data) {
    var block = this.table.closest(".table-block").find(".pagination");
    var li;
    var a;
    var i;
    block.html("");
    for (i = 1; i <= data.countPage; i++){
        li = $("<li/>");
        if(i === data.currentPage){
            li.addClass('active');
        }
        a = $("<a/>").addClass('product-pagination-item').attr('href', '/products?products_page='+i).text(i);
        li.append(a);
        block.append(li)
    }
};

window.Table.prototype.initDatePicker = function () {
    $(".datepicker").datepicker({
        format: 'dd.mm.yyyy'
    });
};

window.Products.prototype.initEvents = function () {
    var self = this;
    $(document).on('click', '.product-pagination-item', $.proxy(this.clickPaginate, self));
    $(document).on('click', '.reviews-product', $.proxy(this.clickReviews, self));
    $(document).on('click', '.add-review', $.proxy(this.clickAddReview, self));
    $(document).on('click', '.delete-review', $.proxy(this.clickDeleteReview, self));
    $(document).on('click', '.create-product', $.proxy(this.clickCreate, self));
    $(document).on('click', '.edit-product', $.proxy(this.clickEdit, self));
    $(document).on('click', '.delete-product', $.proxy(this.clickDelete, self));
    $(document).on('click', '.mass-delete-product', $.proxy(this.massDelete, self));
    $(document).on('click', '.mass-edit-product', $.proxy(this.massEdit, self));
    $(document).on('click', '.mass-update-product', $.proxy(this.massUpdate, self));
    $(document).on('click', '.mass-cancel-product', $.proxy(this.massEditClose, self));
};

window.Products.prototype.init = function () {
    this.initEvents();
    this.loadData();
    this.initDatePicker();
    this.loadSections();
};