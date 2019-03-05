window.Sections = function(){
    this.table = $("#section_table tbody");
    this.link = '/sections';
    this.sections = [];
    $(document).ready($.proxy(this.init, this));
};

window.Sections.prototype = Object.create(Table.prototype);

/**
 * make one section row
 * @param section
 * @returns {jQuery|HTMLElement}
 */
window.Sections.prototype.makeRow = function (section) {
    var self = this;
    var tr = $("<tr/>");
    tr.append($('<td>').html('<input type="checkbox" name="ids[]" value="'+section.id+'" class="checkbox-section"/>'));
    tr.append($('<td>').html(section.id));
    tr.append($('<td>').addClass('name-field')
        .data('value', section.name)
        .html(section.name));
    tr.append($('<td>')
        .addClass('description-field')
        .data('value', section.description)
        .html(section.description));
    tr.append(
        $('<td/>')
            .append(self.makeButton(section.id, 'Edit', 'btn-primary edit-section glyphicon-pencil'))
            .append(self.makeButton(section.id, 'Delete', 'btn-danger delete-section glyphicon-trash'))
    );
    this.table.append(tr);
    return tr;
};

window.Sections.prototype.clickCreate = function (e) {
    e.preventDefault();
    var modal = $("#form-section");
    modal.find('form').attr('method', 'post');
    modal.find('.modal-title').text("Create section");
    modal.find('.ajax-submit-modal').text("Create");
    modal.find('[name=id]').val("");
    modal.find('[name=name]').val("");
    modal.find('[name=description]').val("");
    modal.find('.ajax-submit-modal').off('click');
    modal.find('.ajax-submit-modal').on('click', $.proxy(this.submitFormModal, this));
    modal.modal('show');
};

window.Sections.prototype.clickEdit = function (e) {
    e.preventDefault();
    var self = this;
    var element = e.target;
    var modal = $("#form-section");
    var id = $(element).data('id');
    modal.find('[name=id]').val(id);
    modal.find('.ajax-submit-modal').off('click');
    modal.find('.ajax-submit-modal').on('click', $.proxy(this.submitFormModal, self));
    this.ajaxSubmit({}, 'GET', '/sections/'+id, 'json', function (data) {
        modal.find('form').attr('method', 'put');
        modal.find('.modal-title').text("Edit section");
        modal.find('.ajax-submit-modal').text("Update");
        modal.find('[name=name]').val(data.name);
        modal.find('[name=description]').val(data.description);
        modal.modal('show');
    });
};

window.Sections.prototype.clickDelete = function (e) {
    e.preventDefault();
    var element = e.target;
    var id = $(element).data('id');
    this.confirmModal('Delete section', 'Are you sure you want to delete this section?', $.proxy(this.confirmDelete, this, id));
};

window.Sections.prototype.confirmDelete = function (id) {
    var self = this;
    var modal = $("#confirm-modal");
    self.ajaxSubmit({
        id: id
    }, 'DELETE', '/sections', 'json', function(data){
        self.loadData();
        modal.modal('hide');
    })
};

window.Sections.prototype.massDelete = function (e) {
    e.preventDefault();
    if($("#section_table .checkbox-section:checked").length > 0) {
        this.confirmModal(
            'Delete selected sections',
            'Are you sure you want to delete this selected sections?',
            $.proxy(this.massDeleteConfirm, this));
    }else{
        this.messageModal('Warning', 'No section selected');
    }
};

window.Sections.prototype.massDeleteConfirm = function () {
    var ids = [];
    var self = this;
    $("#section_table .checkbox-section:checked").each(function(){
        ids.push($(this).val())
    });
    self.ajaxSubmit({
        ids: ids
    }, 'DELETE', '/sections/mass-delete', 'json', function(data){
        self.loadData();
        $("#confirm-modal").modal('hide');
    })
};

window.Sections.prototype.massEdit = function (e) {
    e.preventDefault();
    var self = this;
    var element = e.target;
    var checkbox = $("#section_table .checkbox-section:checked");
    if(checkbox.length > 0) {
        $("#section_table input[type=checkbox]").prop('readonly', true);
        $(element).removeClass('mass-edit-section').addClass('mass-update-section').text("Save");
        $(".mass-cancel-section").show();
        checkbox.each(function () {
            var tr = $(this).closest('tr');
            var nameField = tr.find('.name-field');
            var descriptionField = tr.find('.description-field');
            nameField.html("<input name='name[]' type='text' value='" + nameField.data('value') + "' />");
            descriptionField.html("<textarea name='description[]'>"+descriptionField.data('value')+"</textarea>");
        });
    }else{
        self.messageModal('Warning!', 'No product selected');
    }
};

window.Sections.prototype.massUpdate = function (e) {
    e.preventDefault();
    var self = this;
    var form = $("#sections_form");
    self.ajaxSubmit(form.serialize(), form.attr('method'), form.attr('action'), 'json', function(data){
        self.massEditClose();
    })
};

window.Sections.prototype.massEditClose = function () {
    $(".mass-update-section").removeClass('mass-update-section').addClass('mass-edit-section').text("Edit selected");
    $(".mass-cancel-section").hide();
    $("#section_table input[type=checkbox]").prop('readonly', false).prop('checked', false);
    this.loadData();
};

window.Sections.prototype.makePagination = function (data) {
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
        a = $("<a/>").addClass('sections-pagination-item').attr('href', '/sections?sections_page='+i).text(i);
        li.append(a);
        block.append(li)
    }
};

window.Sections.prototype.initEvents = function () {
    var self = this;
    $(document).on('click', '.sections-pagination-item', $.proxy(this.clickPaginate, self));
    $(document).on('click', '.create-section', $.proxy(this.clickCreate, self));
    $(document).on('click', '.edit-section', $.proxy(this.clickEdit, self));
    $(document).on('click', '.delete-section', $.proxy(this.clickDelete, self));
    $(document).on('click', '.mass-delete-section', $.proxy(this.massDelete, self));
    $(document).on('click', '.mass-edit-section', $.proxy(this.massEdit, self));
    $(document).on('click', '.mass-update-section', $.proxy(this.massUpdate, self));
    $(document).on('click', '.mass-cancel-section', $.proxy(this.massEditClose, self));
};

window.Sections.prototype.init = function () {
    this.initEvents();
    this.loadData();
};