/**
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob/Extractor
 *
 * This file does not work
 *
 */


// Clear the console to see better
clear();
console.log('Processing...');


// Add jQuery
var jq = document.createElement('script');
jq.src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js";
document.getElementsByTagName('head')[0].appendChild(jq);



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

if(!companies){
    var companies = ['Nomios','Nomination','Baltazare'];
}

setTimeout(function(){

    search(companies[i]);


},2000);


function buildQuery(company)
{
    var query = ' site:linkedin.com/in/ ';
    query += ' ("' + company + '") AND (';

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

    var input = $('input[name="q"]');

    var button = $('button[value="Rechercher"]');

    input.val(buildQuery(company));
    setTimeout(function(){
        button.click();
    },200);


    setTimeout(function(){
        scrap(company);
    },7000);

}


/**
 *
 * Scrapp all linkedin results and find the jobs
 *
 * @param company
 */
function scrap(company)
{
    var list = $('#ires');


    list.find('.g').each(function(){

        var user = {};

        user.full_name = $(this).find('h3 a').text().replace(/ \| LinkedIn/,'');
        user.linkedin = $(this).find('h3 a').attr('href');
        user.title = $(this).find('.slp').html();

        user.supposed_company = company;
        user = breakTitle(user);

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
    var title = user.title.split(/ - /);

    user.address = title[0];
    user.position = title[1];
    user.company = title[2];



    // List of all jobs you want to remove
    if(user.position.match(/(Chef de projet|^Intern$|stagiaire|Freelance|Human Resource|^UX |intégrateur ERP|Customer Care Manager|Professeur|Project Technical Manager|Chargée? de Recrutement|CRM Manager|^CDP$|Chargée? de projet|Developpeur|Data Scientist|Developer|Devops|Data Analyst|Directeur de Production|^Consultant$|Lead generation|événementiel|Customer Success Manager|Country Manager|Internship|Machine Learning|Web developer|Scrum master|Développeur|Business Developer|Coach|Ressources? Humaines?|Traffic Manager|PS Consultant|Product Manager|community manager|Ingénieur|informatique|Account Manager|stagiaire|teacher|responsable produit|Webmaster|Artist|Hygiène|Ingénieur Système|Logiciel|Développeur|Architecte|Creative|Projects? Manager|technique|Sales|Stage |Talent Acquisition|Marketing|Communications|designer|Commercial|^CTO$)/ig)){
        return false;
    }


    if(!user.company || !user.supposed_company){
        return false;
    }

    user.same_company = null != (user.company.match(new RegExp(user.supposed_company,"gim")) || user.supposed_company.match(new RegExp(user.company,"gim")));


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