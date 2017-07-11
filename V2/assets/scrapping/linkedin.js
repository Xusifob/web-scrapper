/**
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob/Extractor
 *
 * This file is to copy/paste in the LinkedIn Console to get information about some people in some companies
 *
 *
 * LinkedIn may block you from using the search if you abuse of this use, I decline responsibility in case of maluse
 *
 *
 */


// Clear the console to see better
clear();
console.log('Processing...');


// https://www.linkedin.com/search/results/people/?facetGeoRegion=["fr:0"]&facetNetwork=["F","S","O"]


var jobs = [
    'CFO',
    'DAF',
    "Financier",
    'chief financial officer',
    'Directeur Financier',
    "Directeur Administratif et Financier",
    'CEO',
    "Crédit Manager",
    "Secrétaire Générale",
    "Assistant",
    "Comptable"
];

var countries = [
    'France'
];


var users = [];

var i = 0 ;

search(companies[i]);


function buildQuery(company)
{
    var query = ' ("' + company + '") AND (';

    $.each(jobs,function(key,value){
        query += '"'+ value +'"';
        if(key != jobs.length-1){
            query += ' OR ';
        }
    });

    query += ') AND ( ';
    $.each(countries,function(key,value){
        query += '"'+ value +'"';
        if(key != countries.length-1){
            query += ' OR ';
        }
    });


    query += ')';

    return query;

}

function search(company)
{

    var input = $('input[placeholder="Search"]');

    var button = $('.submit-button');

    input.val(buildQuery(company));
    button.click();

    setTimeout(function(){
        if($('.search-facet--current-company ol li:first-child input').is(':checked')){
            $('.search-facet--current-company ol li:first-child label').click();//.prop('checked', false);
        }

        setTimeout(function(){
            var label = $('.search-facet--current-company ol li:first-child label');
            var txt = label.find('div').text().trim();


            if(company.match(new RegExp(txt,"gim")) || txt.match(new RegExp(company,"gim"))){
                label.click();
                console.log('click !');
                //.prop('checked', true);
            }

            setTimeout(function(){
                scrap(company);
            },3000);
        },3000)
    },3000)
}


/**
 *
 * Scrapp all linkedin results and find the jobs
 *
 * @param company
 */
function scrap(company)
{
    var list = $('.results-list');


    list.find('li').each(function(){

        var user = {};

        user.full_name = $(this).find('.actor-name').html();
        user.linkedin = 'https://www.linkedin.com' + $(this).find('.search-result__result-link').attr('href');
        user.title = $(this).find('.search-result__snippets').html();

        user = breakTitle(user);
        user.supposed_company = company;

        if(user){
            users.push(user);
        }
    });


    i++;

    if(companies[i]){
        console.log(company + ' scrapped');
        console.log(users);
        search(companies[i]);
    }else{
        console.log(users);
        download(ConvertToCSV(JSON.stringify(users)));

    }

}


/**
 *
 * Break the linkedin
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

    user.position = job[0].replace(/^Current:/i,'').trim();


    // List of all jobs you want to remove
    if(user.position.match(/(Chef de projet|^Intern$|Freelance|Human Resource|^UX |intégrateur ERP|Customer Care Manager|Professeur|Project Technical Manager|Chargée? de Recrutement|CRM Manager|^CDP$|Chargée? de projet|Developpeur|Data Scientist|Developer|Devops|Data Analyst|Directeur de Production|^Consultant$|Lead generation|événementiel|Customer Success Manager|Country Manager|Internship|Machine Learning|Web developer|Scrum master|Développeur|Business Developer|Coach|Ressources? Humaines?|Traffic Manager|PS Consultant|Product Manager|community manager|Ingénieur|informatique|Account Manager|stagiaire|teacher|responsable produit|Webmaster|Artist|Hygiène|Ingénieur Système|Logiciel|Développeur|Architecte|Creative|Projects? Manager|technique|Sales|Stage |Talent Acquisition|Marketing|Communications|designer|Commercial|^CTO$)/ig)){
        return false;
    }

    user.company = job[1].trim();

    return user;
}


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