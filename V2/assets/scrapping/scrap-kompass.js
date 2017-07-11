/**
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob/Extractor
 *
 * This file is to copy/paste in the Kompass Console to get information about some companies
 *
 *
 * Kompass may block you from using the search if you abuse of this use, I decline responsibility in case of misuse
 *
 *
 */


// Clear the console to see better
clear();

// Add jQuery
var jq = document.createElement('script');
jq.src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js";
document.getElementsByTagName('head')[0].appendChild(jq);

console.log('Processing...');

var companies = [];

function ConvertToCSV(objArray) {


    var array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray;
    var str = '';

    console.log('exporting ' + array.length + ' companies');


    for (var i = 0; i < array.length; i++) {
        var line = '';

        // Add headers
        if(i == 0){
            for (var index in array[i]) {
                if (line != '') line += ';';

                line += index;
            }

            str += line + '\r\n';
            var line = '';
        }




        for (var index in array[i]) {
            if (line != '') line += ';';

            line += array[i][index];
        }

        str += line + '\r\n';
    }

    return str;
}


function download(text, name, type) {
    var a = document.getElementById("a");
    var file = new Blob([text], {type: type});
    window.open(URL.createObjectURL(file));

    console.log('accept pop-up or copy and paste this link ' + URL.createObjectURL(file) );


}

// Timeout to make sure jQuery is loaded
setTimeout(function(){

    var links =  jQuery.find('#companies-table-content tr');

    // Pour toutes les entreprises
    for(var i = 0; i< links.length; i++){
        var company = {};

        // Je récupère les infos
        company.company =  $(links[i]).find('[id^="company-detail"]').text();

        company.phone =  $(links[i]).find('.company_phone').attr('title');

        company.address =  $($(links[i]).find('td')[15]).attr('title') + ' ' + $($(links[i]).find('td')[7]).attr('title') + ' ' + $($(links[i]).find('td')[6]).attr('title');

        company.CA =  $($(links[i]).find('td')[13]).attr('title').replace('�','à');

        companies.push(company);


    }

    download(ConvertToCSV(JSON.stringify(companies)));

},2000);