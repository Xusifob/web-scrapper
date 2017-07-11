/**
 *
 * List of all jobs we are looking for
 *
 * @type {string[]}
 */
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
    //  "Assistant",
    "Directeur Comptable",
    "Office Manager",
    "Comptable",
    "Responsable Recouvrement",
    "Chargé de recouvrement",
    "‎Responsable facturation"
];


/**
 *
 * List of all countries we are looking for
 *
 * @type {string[]}
 */
var countries = [
    'France'
];


/**
 *
 * All the users we are exporting
 *
 * @type {Array}
 */
var users = JSON.parse(localStorage.getItem('_users'));

if(!users){
    users = [];
}

var _iterator = parseInt(localStorage.getItem('_company_iterator'));


if(!_iterator){
    _iterator = 0 ;
}

/**
 *
 * Get previous companies
 *
 * @type {any}
 */
var companies = JSON.parse(localStorage.getItem('_companies'));

/**
 * Set the current companies
 */
if(!companies){
    var companies = '%COMPANIES%';

    localStorage.setItem('_companies',JSON.stringify(companies));

}

// Add Jquery
var script = document.createElement('script');
script.src = 'https://code.jquery.com/jquery-2.0.0.min.js';
script.type = 'text/javascript';
document.getElementsByTagName('head')[0].appendChild(script);


// Launch Process
setTimeout(function(){

    if(!document.location.href.match('site:linkedin.com')){
    searchNextCompany(companies[_iterator]);
    }else{
        search(companies[_iterator]);
    }

},2000);


/**
 *
 * Build a Lnikedin Google Query
 *
 * @param company
 * @returns {*}
 */
function buildQuery(company)
{
    var query = ' site:linkedin.com/in/ ';
    query += ' ("' + company + '") AND (';

    for (var key = 0 ; key < jobs.length;key++ ){
        var value = jobs[key];
        query += '"'+ value +'"';
        if(key != jobs.length-1){
            query += ' OR ';
        }
    }

    query += ') AND ( ';

    for (var countries = 0 ; key < jobs.length;key++ ){
        query += '"'+ value +'"';
        if(key != countries.length-1){
            query += ' OR ';
        }
    }


    query += ')';

    return query.replace(/&/gi,'%26');

}


/**
 *
 * Do a search
 *
 * @param company
 */
function search(company)
{

    var _interval = setInterval(function(){

        if(document.querySelector('input[name="q"]')){

            clearInterval(_interval);
            setTimeout(function(){
                _scrap(company);
            },Math.random() * 5000 + 1000);
        }

    },1000);
}


/**
 *
 * Go on next company page
 *
 * @param company
 */
function searchNextCompany(company)
{
    document.location.href = 'https://www.google.fr/search?q=' + buildQuery(company);
}


/**
 *
 * Do the magic !
 *
 * @param company
 * @private
 */
function _scrap(company){

    var list = document.getElementById('ires');

    var elems = list.querySelectorAll('.g');

    for (var w = 0 ;w < elems.length;w++){
        var user = {};

        var elem = elems[w];

        user.full_name = elem.querySelectorAll('h3 a')[0].innerText.replace(/(\(.+\) sur ? Linkedin)|(\| linkedin)|(\| Profil professionnel - Linkedin)|( chez .+)/i,'');
        user.linkedin = elem.querySelectorAll('h3 a')[0].getAttribute('href');
        user.linkedin_title = elem.querySelectorAll('.slp')[0] ? elem.querySelectorAll('.slp')[0].innerText : '';

        user.company = company;

        user = breakTitle(user);

        if(user){
            users.push(user);
        }

    }

    _iterator++;

    console.log(companies[_iterator]);

      if(companies[_iterator]){

          localStorage.setItem('_company_iterator',_iterator);
          localStorage.setItem('_users',JSON.stringify(users));

          searchNextCompany(companies[_iterator]);

    }else{
          var data = ConvertToCSV(users);

          localStorage.removeItem('customjs');
          localStorage.removeItem('_company_iterator');
          localStorage.removeItem('_companies');

          document.body.innerHTML = '<pre>' + data + '</pre>';
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

    if(!user.linkedin_title || user.linkedin_title == ''){
        return false;
    }
    var title = user.linkedin_title.split(/ - /);

    user.linkedn_company = title[2];
    user.address = title[0];
    user.title = title[1];

    if(!user.address || !user.title || !user.linkedn_company){
        return false;
    }

    user.address = user.address.trim();
    user.title = user.title.trim().replace(/ (chez|at|@) .+/igm,'');
    user.linkedn_company = user.linkedn_company.trim();


    if(!user.address.match(/France/igm)){
        return false;
    }



    // List of all jobs you want to remove
    if(user.title.match(/(Affiliat|cuisinier|Brand Manager|promotion|(é|e)dition|‎h(o|ô)te(sse)?|ouvrier|tCopywriter|Chief Technology Officer|attach(e|é) de presse|‎Art Director|retrait(e|é)|En poste|juriste|coiffeu(r|se)|RECRU(I)?T(e)?MENT|écoute|(é|e)l(é|e)ctr(on)?i(cien|que)|étudi(e|é)|looking|webdesign|Ing(e|é)nieur|mobilité|courtier|^Manager$|Banquier|^Analyst$|HR Manager|Reconversion|fonctionnaire|élève|profess(eu|o)r|journaliste|editoial|p(é|e)dagogi(e|que)|Producteur|Big Data|apprenti|Business Manager|Happiness Manager|traduct(rice|eur|or)|Data Analytics|Javascript|Chef de pro(jet|duit)|‎Apprenti|ind(e|é)pendant|Student|recherche|Support|Head ?Hunter|Customer Happiness|formation|patrimoine|Tech |( intern|intern )|Publicit(é|e)|CTO$|Capital Risque|Trader|Growth|Chargée? d'affaires?|CMO |Dessinateur|Contenu|projet|Contr(ô|o)leur|Business D(é|e)velope?mm?ent( Manager)?|‎business developper|Archiviste|gestion de projet|labo|Documentaliste|Standardiste|Architect|Market|Acheteur|Administrateur (syst(e|è)me? )?( ?et ?)?r(e|é)seau|auteur|SEO|Product Strategy|Supply Chain|Graphiste|maintenance|export|Scientist|Bureau d'Etudes|Systèmes? (d')?Information|digital strategy|IT Manager|appels? d'offres|R(é|e)dacteur| IT |Product Management|Customer Success|DSI |Juridique|santé|logistique|logisti(que|c)|Engineer|Conseiller|Affaires Publiques|clientèle|Service Client|support client|Conducteur|chantier|consulting|consultant|commercial|juridique|communication|cin(e|é)ma|Contrôle(ur)? de Gestion|Clientèle|avocat|livraison|concept(eur|rice)|Fundraising|Responsable de production|ventes?| HR |sécurité|cr(é|e)ation|RH|Producer|Format(eur|rice)|Chargé de Mission|géologue|--|R&D|Chief People Officer|Back Office|Chef de Service|industriel|(é|E)tudiant|Software Engineer|enseignant|Recrutement|programmeur|Technicien|DRH|achat|CTO |Chef de Produit|Responsable Recrutement|^Intern$|stagiaire|Freelance|Human Resource|^UX |intégrateur ERP|Customer Care Manager|Professeur|Project Technical Manager|Chargée? de Recrutement|CRM Manager|^CDP$|Chargée? de projet|Developpeur|Data Scientist|Developer|Devops|Data Analyst|Directeur de Production|^Consultant$|Lead generation|événementiel|Customer Success Manager|Country Manager|Internship|Machine Learning|Web developer|Scrum master|Développeur|Business (Developer|Development)|Coach|Ressources? Humaines?|Traffic Manager|PS Consultant|Product Manager|community manager|Ingénieur|informatique|Account Manager|stagiaire|teacher|responsable produit|Webmaster|Artist|Hygiène|Ingénieur Système|Logiciel|Développeur|Architecte|Creative|Projects? Manager|technique|Sales?|Stage |Talent Acquisition|Marketing|Communications|designer|Commercial|^CTO$)/ig)){
        return false;
    }




    if(!user.company || !user.linkedn_company){
        return false;
    }

    user.same_company = null != (user.company.match(new RegExp(RegExp.escape(user.linkedn_company),"gim")) || user.linkedn_company.match(new RegExp(RegExp.escape(user.company),"gim")));

    user.similarity = 1;


    user.correct_job = null != user.title.match(/(^‎Président$|‎Chief Financial Officer|Technical Director|contrôleur financier|Finance Director|‎Secrétaire Général|Directeur Général|Group CFO|‎Direction Comptable|Responsable Comptable|Office Manager|^CEO$|^CFO$|Direct(eur|rice) Financier|^DAF$|(Responsable|Directeur) Administrati(f|ve) (et )?Financi(e|è)re)/gim);

    if(!user.same_company){
        user.similarity = similarity(user.company,user.linkedn_company);

        if(user.similarity > 0.6){
            user.same_company = true;
        }

        if(user.similarity < 0.15){
            return false;
        }

        // List of all jobs you want to remove
        if(user.linkedn_company.match(/(Universit(é|e)|Auto( |-)entrepreneur|looking|Etudiant|lib(e|é)rale?|chomage|ind(é|e)pend(a|e)nt|freelance)/ig)){
            return false;
        }


    }

    return user;
}


/**
 *
 * Convert
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
 * Calculate similarity between 2 strings
 *
 * @param s1
 * @param s2
 * @returns {number}
 */
function similarity(s1, s2) {
    var longer = s1;
    var shorter = s2;
    if (s1.length < s2.length) {
        longer = s2;
        shorter = s1;
    }
    var longerLength = longer.length;
    if (longerLength == 0) {
        return 1.0;
    }
    return (longerLength - editDistance(longer, shorter)) / parseFloat(longerLength);
}


RegExp.escape = function(s) {
    return s.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
};


/**
 *
 * Edit distance between 2 strings
 *
 * @param s1
 * @param s2
 * @returns {any|*}
 */
function editDistance(s1, s2) {
    s1 = s1.toLowerCase();
    s2 = s2.toLowerCase();

    var costs = new Array();
    for (var i = 0; i <= s1.length; i++) {
        var lastValue = i;
        for (var j = 0; j <= s2.length; j++) {
            if (i == 0)
                costs[j] = j;
            else {
                if (j > 0) {
                    var newValue = costs[j - 1];
                    if (s1.charAt(i - 1) != s2.charAt(j - 1))
                        newValue = Math.min(Math.min(newValue, lastValue),
                                costs[j]) + 1;
                    costs[j - 1] = lastValue;
                    lastValue = newValue;
                }
            }
        }
        if (i > 0)
            costs[s2.length] = lastValue;
    }
    return costs[s2.length];
}

