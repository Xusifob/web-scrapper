/**
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob/Extractor
 *
 * This file is to copy/paste in the french Tech Console to get information about some people in some companies
 *
 *
 * frenchTech may block you from using the search if you abuse of this use, I decline responsibility in case of maluse
 *
 *
 */


clear();

var companies = [];

jQuery('#container').find('div[id^="post-"]').each(function(){
    var company = {};

    company.company = $(this).find('#presentation-tableau1 a').text();
    company.address = $(this).find('#presentation-tableau1').text().replace(company.company,'');

    companies.push(company);

});

console.log(companies);



function ConvertToCSV(objArray) {


    var array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray;
    var str = '';

    console.log('exporting ' + array.length + ' contacts');


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

    return str.replace(/&amp;/gi,'&');
}


function download(text, name, type) {
    var a = document.getElementById("a");
    var file = new Blob([text], {type: type});
    window.open(URL.createObjectURL(file));

    console.log('accept pop-up or copy and paste this link ' + URL.createObjectURL(file) );


}