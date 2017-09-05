//JS function to get the id of the click element and replace the image via AJAX call

function replace_cat(clicked_id){
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {

            var xmlDoc = this.responseXML;
            var x = xmlDoc.getElementsByTagName('url')[0];
            var y = x.childNodes[0];
            document.getElementById(clicked_id).src=y.nodeValue;

        }
    };
    xhttp.open("GET", "http://thecatapi.com/api/images/get?format=xml&results_per_page=1", true);
    xhttp.send();
}