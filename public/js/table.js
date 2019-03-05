/**
 * Main table class
 * @constructor
 */
window.Table = function(){};

window.Table.prototype.clearTable = function () {
    this.table.html("");
};

/**
 * simple ajax submit method
 * @param data
 * @param type
 * @param link
 * @param dataType
 * @param successFunction
 * @param failFunction
 */
window.Table.prototype.ajaxSubmit = function (data, type, link, dataType, successFunction, failFunction) {
    var self = this;
    if(link === undefined){
        link = window.location.pathname;
    }
    if(dataType === undefined){
        dataType = 'html';
    }
    if(data === undefined){
        data = {};
    }
    if(type === undefined){
        type = "GET";
    }
    if(typeof successFunction !== 'function'){
        successFunction = function (data) {
            console.log(data);
        }
    }
    if(typeof failFunction !== 'function'){
        failFunction = function (jqXHR, textStatus) {
            console.log(jqXHR.responseJSON);
        }
    }
    $.ajax({
        url: link,
        type: type,
        data: data,
        dataType: dataType
    }).done(successFunction).fail(failFunction);
};

/**
 * ajax load date and make rows
 */
window.Table.prototype.loadData = function () {
    var self = this;
    this.ajaxSubmit({}, 'GET', this.link, 'json', function (data) {
        self.clearTable();
        self.makePagination(data);
        $.each(data.items, function(){
            self.makeRow(this);
        });
    });
};

window.Table.prototype.makeButton = function (id, title, classes) {
    var block = $("<span/>");
    block.data('placement', 'top');
    block.data('toggle', 'tooltip');
    block.attr('title', title);
    var button = $("<button/>");
    button.addClass('btn btn-xs glyphicon');
    button.addClass(classes);
    button.data('id', id);
    block.append(button);

    return block;
};

window.Table.prototype.clickPaginate = function (e) {
    e.preventDefault();
    var element = e.target;
    this.link = $(element).attr('href');
    this.loadData();
};

/**
 * main method to handle submit modal form
 * @param e
 */
window.Table.prototype.submitFormModal = function (e) {
    e.preventDefault();
    var self = this;
    var element = e.target;
    var modal = $(element).closest('.modal');
    var form = modal.find('form');
    self.ajaxSubmit(form.serialize(), form.attr('method'), form.attr('action'), 'json',function(data){
        self.loadData();
        modal.modal('hide');
    }, function(){
        self.messageModal('Error!', 'Internal server error');
    });
};

/**
 * information modal
 * @param title
 * @param message
 */
window.Table.prototype.messageModal = function (title, message) {
    var modal = $("#message-modal");
    modal.find('.modal-title').text(title);
    modal.find('.modal-message').text(message);
    modal.modal('show');
};

/**
 * modal with confirmation
 * @param title
 * @param question
 * @param callback
 */
window.Table.prototype.confirmModal = function (title, question, callback) {
    var modal = $("#confirm-modal");
    modal.find('.modal-title').text(title);
    modal.find('.modal-question').text(question);
    modal.find('.confirmed').off('click');
    modal.find('.confirmed').on('click', callback);
    modal.modal('show');
};
