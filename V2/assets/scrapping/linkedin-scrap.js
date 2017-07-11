// Changez ces valeurs pour avoir la page de fin de recherche
var end = 400;



// Clear the console to see better
clear();
console.log('Processing...');


var users = [];

setTimeout(function(){
    scrap();
},Math.random()*10000 + 5000);

/**
 *
 * Scrap all LinkedIn results and find the jobs
 *
 */
function scrap()
{
    var list = $('.results-list');


    list.find('li').each(function(){

        var user = {};

        user.full_name = $(this).find('.actor-name').html();
        user.linkedin = 'https://www.linkedin.com' + $(this).find('.search-result__result-link').attr('href');
        user.title = $(this).find('.search-result__snippets').html();
        user.address = $(this).find('.subline-level-2').text().trim();

        user = breakTitle(user);

        if(user){
            users.push(user);
        }
    });


    if(getCurrentPage() >= end || jQuery('.next').length == 0){
        download(ConvertToCSV(users));
    }else{
        jQuery('.next').click();

        setTimeout(function(){
            scrap();
        },Math.random()*10000 + 5000)
    }


}



/**
 *
 * Break the LinkedIn
 *
 * @param user
 * @returns {*}
 */
function breakTitle(user){


    if(!user.title){
        return false;
    }

    if(user.full_name == 'LinkedIn Member'){
        return false;
    }

    user.title = user.title.replace(/(<[^>]+>|\r?\n|\r)/gim,'');
    user.title = user.title.replace(/&amp;/g, '&');


    if(!user.title.match(/^Current:/i)){
        return false;
    }


    var job = user.title.split(/ at /);

    if(!job[0] || !job[1]){
        return false;
    }

    user.title = job[0].replace(/^Current:/i,'').trim();


    user.company = job[1].trim();

    return user;
}


/**
 *
 * Convert an object to a CSV string
 *
 *
 * @param objArray
 * @returns {*}
 * @constructor
 */
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


/**
 *
 * Download a file
 *
 * @param text
 * @param name
 * @param type
 */
function download(text, name, type) {
    var a = document.getElementById("a");
    var file = new Blob([text], {type: type});
    window.open(URL.createObjectURL(file));

    console.log('accept pop-up or copy and paste this link ' + URL.createObjectURL(file) );

}



/**
 *
 * @returns {number|Number}
 */
function getCurrentPage(){

    return parseInt($('.page-list .active').text().trim());
}