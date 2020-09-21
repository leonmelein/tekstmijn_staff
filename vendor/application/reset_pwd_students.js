/**
 * Created by leon on 26-01-17.
 */
function reset_student_pwd(id,document) {
    console.log(id);
    console.log(document);


    $.getJSON( "/staff/reset/" + id, function( data ) {
        console.log(data);
        var btn = document.getElementById(id);
        btn.innerHTML  = "<span class='green'><i class='glyphicon glyphicon-ok'></i> Wachtwoord gereset.</span>";
        console.log("Reset!");
    });
}
