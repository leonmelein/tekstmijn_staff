function addPencil(input, button) {
    console.log(input);
    console.log(button);
    contentdiv = input.childNodes[3];
    console.log(contentdiv);
    contentdiv.style.display = "inline";
    button.parentNode.innerHTML="<button id='add_button' type='button' onclick='removePencil(this.parentNode.parentNode.parentNode, this)' class='btn btn-default'><i class='glyphicon glyphicon-floppy-disk'></i></button>";
}

function removePencil(input, button) {
    button.parentNode.innerHTML="<button id='add_button' type='button' onclick='addPencil(this.parentNode.parentNode.parentNode, this)' class='btn btn-default'><i class='glyphicon glyphicon-pencil'></i></button>";
    textfield = input.childNodes[3];
    textfield.style.display = "none";

}

$(document).ready(function() {
    // bind submit handler to form
    $('#grade').on('submit', function(e) {
        e.preventDefault(); // prevent native submit
        $(this).ajaxSubmit({
            target: 'myResultsDiv'
        });
        alert("De beoordeling is opgeslagen!");
    });
});