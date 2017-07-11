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

jQuery('#hits').find('.ais-hits--item').each(function(){
    var company = {};

    company.company = $(this).find('.product-name').text();
    company.website = $(this).find('.product-url').text();
    company.category = $(this).find('.span-product-cat').text();
    company.address = $(this).find('.span-product-hub').text();
    company.description = $(this).find('.product-type').text();

    companies.push(company);

});



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