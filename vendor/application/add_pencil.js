function addPencil() {
    var div_form_group = document.createElement('div');
    div_form_group.className = 'row';
    div_form_group.innerHTML = '' +
        '<div class="col-md-9">' +
        '</br>' +
        '<textarea name="grade_Opmerkingen" class="form-control input-md" rows="3"></textarea>' +
        '</div>';
    document.getElementById('content').appendChild(div_form_group);
    this.value=="test";
}

function removeRow(input) {
    document.getElementById('content').removeChild(input.parentNode);
}