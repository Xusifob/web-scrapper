/**
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob/Extractor
 *
 * This file allows you to get into a JSON string the list of all companies webpage on Masteseek.com
 *
 *
 */

var a = [] ;

var links =  jQuery.find('#divCompanies .fileview a');

for(var i = 0; i< links.length; i++){
    a.push(links[i].getAttribute('href'));
}
console.log(JSON.stringify(a));