function addPencil(input, button) {
    contentdiv = input.childNodes[3];
    contentdiv.style.display = "inline";
    button.parentNode.innerHTML="<button id='add_button' type='button' onclick='removePencil(this.parentNode.parentNode.parentNode, this)' class='btn btn-default'><i class='glyphicon glyphicon-floppy-disk'></i></button>";
}

function removePencil(input, button) {
    button.parentNode.innerHTML="<button id='add_button' type='button' onclick='addPencil(this.parentNode.parentNode.parentNode, this)' class='btn btn-default'><i class='glyphicon glyphicon-pencil'></i></button>";
    textfield = input.childNodes[3];
    textfield.style.display = "none";

}

$(document).ready(function() {
    $('.grade').on('submit', function(e) {
        e.preventDefault(); // prevent native submit
        $(this).ajaxSubmit({
            target: 'myResultsDiv'
        });
        submission_id = $(this).serializeArray()[2]['value'];
        content_field = 'content_'+submission_id;
        notes_button = 'notes_button_'+submission_id;
        current_row = '#students_'+submission_id;
        document.getElementById(content_field).style.display = "none";
        document.getElementById(notes_button).innerHTML="<button id='add_button' type='button' onclick='addPencil(this.parentNode.parentNode.parentNode, this)' class='btn btn-default'><i class='glyphicon glyphicon-pencil'></i></button>";
        $(current_row).animate({backgroundColor: "#7CCC6D"}, 'medium');
        setTimeout("$(current_row).animate({backgroundColor: ''}, 'medium');",1000);
    });
});

function saveAll(input) {
    submissions = input.split(",");
    submissions.forEach(function(submission){
        form_id = '#grade_'+submission;
        $(form_id).ajaxSubmit({
            target: 'myResultsDiv'
        })
        content_field = 'content_'+submission;
        notes_button = 'notes_button_'+submission;
        document.getElementById(content_field).style.display = "none";
        document.getElementById(notes_button).innerHTML="<button id='add_button' type='button' onclick='addPencil(this.parentNode.parentNode.parentNode, this)' class='btn btn-default'><i class='glyphicon glyphicon-pencil'></i></button>";
    });
    $('.students').animate({backgroundColor: "#7CCC6D"}, 'medium');
    setTimeout("$('.students').animate({backgroundColor: ''}, 'medium');",1000);
}