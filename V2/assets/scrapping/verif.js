var  date = new Date();
var filename = date.getDate() + '-' +  date.getMonth()  + '-'  + date.getHours()  + '-'  + document.location.host.replace('.','-') + '.csv';
var companies = [];
var url = 'http://projets.bastienmalahieude.fr/growth-hacking/V2/save.php?file_name=' + filename;



jQuery('#verif_tableResult').find('tr').each(function(){


    if(!jQuery(this).attr('class')){
        return;
    }

    var addr = jQuery(this).find('.verif_col1 a').attr('href');

    addr =  addr.replace(/\//g,'');

    addr = addr.split('-');

    var siret = addr[addr.length-1];

    var company = {
        company : jQuery(this).find('.verif_col1').text(),
        adresse : jQuery(this).find('.verif_col2').text() + ' ' + jQuery(this).find('.verif_col3').text(),
        turnover : jQuery(this).find('.verif_col5').text(),
        siret : siret
    };


    companies.push(company);

});

jQuery.ajax({
    url: url,
    method : 'POST',
    xhrFields: {
        withCredentials: true
    },
    crossDomain: true,
    username : 'dunforce',
    data: JSON.stringify(companies),
    contentType: "application/json; charset=utf-8",
    password : 'Barcelona',
    dataType: "json",
    success : _success,

}).done(_success);


setTimeout(_success,2000);

function _success(){
    console.clear();
    console.log('Saved ! ');

    if(jQuery('.btn-next').length > 0){
        document.location.href = jQuery('.btn-next').attr('href');
    }else{
        jQuery('body').append('<iframe id="download-iframe" src="'+ url +'&export" "></iframe>');
        console.log('Check your downolads !');
    }
}