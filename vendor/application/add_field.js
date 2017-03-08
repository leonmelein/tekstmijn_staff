function addRow() {
    var div_form_group = document.createElement('div');
    div_form_group.className = 'form-group';
    div_form_group.innerHTML = '' +
        '<div class="col-md-6">' +
        '<input name="grading_name[]" type="text" placeholder="Type beoordeling" class="form-control input-md">' +
        '</div>' +
        '<div class="col-md-4">' +
        '<input name="grading_grade[]" type="number" placeholder="8,0" min="1.0" max="10.0" step="0.1" class="form-control input-md">' +
        '</div>' +
        '<div class="col-xs-2">' +
        '<button id="remove_button" type="button" onclick="removeRow(this.parentNode)" class="btn btn-default"><i class="glyphicon glyphicon-minus"></i></button>' +
        '</div>';
    document.getElementById('content').appendChild(div_form_group);
}

function removeRow(input) {
    document.getElementById('content').removeChild(input.parentNode);
}