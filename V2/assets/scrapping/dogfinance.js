var interval = setInterval(function(){

    $('#scrollbar_participant').animate({ scrollTop: $('#content_list_participants').height() }, 100);
},1000);


function download(text, name, type) {
    var a = document.getElementById("a");
    var file = new Blob([text], {type: type});
    window.open(URL.createObjectURL(file));

    console.log('accept pop-up or copy and paste this link ' + URL.createObjectURL(file) );


}




setTimeout(function(){

    clearInterval(interval);

    var list = $('#content_list_participants');

    var users = [];

    list.find('.a_user ').each(function(key,value){

        var user = {
            url : $(this).find('.a_user_p a').attr('href'),
            name : $(this).find('.a_user_auth a').text(),
            position: $(this).find('.fonction').text(),
        };

        users.push(user);
    });

    download(JSON.stringify(users));

},250000);