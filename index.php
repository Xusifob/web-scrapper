<?php


//

$dirs = array();

$tmp = scandir('./exports/');

foreach($tmp as $d){
    if(is_dir("./exports/$d") and $d != "." && $d != ".."){
        $dirs[] = $d;
    }
}

sort($dirs);

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Extracteur</title>

    <link rel="stylesheet" href="V2/assets/css/style.css">
</head>
<body>


<div class="loading-container">
    <div class="sk-circle">
        <div class="sk-circle1 sk-child"></div>
        <div class="sk-circle2 sk-child"></div>
        <div class="sk-circle3 sk-child"></div>
        <div class="sk-circle4 sk-child"></div>
        <div class="sk-circle5 sk-child"></div>
        <div class="sk-circle6 sk-child"></div>
        <div class="sk-circle7 sk-child"></div>
        <div class="sk-circle8 sk-child"></div>
        <div class="sk-circle9 sk-child"></div>
        <div class="sk-circle10 sk-child"></div>
        <div class="sk-circle11 sk-child"></div>
        <div class="sk-circle12 sk-child"></div>
    </div>
</div>


<form action="#" method="POST" id="form" class="form">

    <div class="error-bandeau error-quota">Quota Email Hunter Atteint ! Créez un nouveau compte ! </div>
    <div class="error-bandeau error-captcha">Quota Google Search Atteint ! Sois un peu plus discret! </div>
    <div class="error-bandeau error-maps-quota">Quota Google Maps Atteint ! Récupérez une nouvelle clé API! </div>
    <div class="error-bandeau error-csv-invalid">Please upload a CSV with company field </div>
    <div class="error-bandeau error-not-csv">Uploadez un fichier au format CSV</div>
    <div class="success-bandeau success-finished">Scrapping Fini</div>
    <div class="success-bandeau success-stopped">Scrapping Bien arrêté</div>


    <h3>Extraire les données d'une entreprise </h3>

    <a class="p-50 tab active" href="#simple">Import simple</a>
    <a class="p-50 tab" href="#bulk">Import via fichier</a>

    <div class="tabset" id="simple">
        <p>Séléctionnez une entreprise</p>
        <input required type="text" id="company" name="name" placeholder="Nom de l'entreprise">
        <input type="text" name="website" placeholder="Site web de l'entreprise">
        <input type="text" name="first_name" placeholder="Prénom">
        <input type="text" name="last_name" placeholder="Nom">
        <input type="text" name="position" placeholder="Poste">
    </div>
    <div class="tabset" style="display: none;" id="bulk">
        <p>importez un fichier CSV</p>
        <input type="file" name="file" id="file">
        <a href="V2/data/template-default.csv" target="_blank" style="text-align: left">Télécharger le fichier exemple</a>
        <a href="javascript:" target="_blank" id="open-code"  style="text-align: left; display: none;">Voir le code Linkdedin</a>

        <div class="progress">
            <div class="progress--content"></div>
        </div>

        <p>Contacts à importer : <span id="current">0</span>/<span id="imported">0</span></p>
        <p>Contacts scrappés : <span id="successed"></span></p>
        <p>Contacts erreur : <span id="errored"></span></p>
        <ul>
            <li>Already in base : <span id="already"></span></li>
            <li>Search : <span id="searched"></span></li>
            <li>Email not found : <span id="email"></span></li>
        </ul>

        <a id="stop" style="text-align: left; color: red;" href="#null">Arreter le scrapping !</a>

    </div>
    <a id="more" class="more-filters" href="#null">Plus d'options</a>

    <div class="more">
        <div>
            <label for="">
                <input type="checkbox" checked name="societe_only" id="societe_only">
                Ne pas scrapper avec Crunch Base
            </label>
        </div>
        <div>
            <label for="">
                <input type="checkbox" name="re_scrap" id="re_scrap">
                re scrap des contacts déjà récupérés
            </label>
        </div>

        <div>
            <label for="">
                <input type="checkbox" checked name="scrap_verif" id="scrap_verif">
                Tenter de récupérer des informations via d'autres providers (Linkedin, Verif...)
            </label>
        </div>
        <div>
            <label for="">
                <input type="checkbox" name="scrap_ceo" id="scrap_ceo">
                Récupérer les CEOS, Directeurs Généraux, Gérants...
            </label>
        </div>
        <div>
            <label for="search_engine">
                Choisir le moteur de recherche à utiliser
            </label>
            <select name="search_engine" id="search_engine">
                <option value="Bing" selected>Bing</option>
                <option value="Google">Google</option>
            </select>
        </div>

        <div>
            <label for="api_hunter"> Clé API Hunter.io</label>
            <input type="text" name="api_hunter" id="api_hunter" placeholder="Clé API Hunter.io">
        </div>

        <div>
            <label for="api_hunter_email"> E-mail du compte Hunter.io</label>
            <input type="email" name="api_hunter_email" id="api_hunter_email" placeholder="E-mail du compte Hunter.io">
        </div>
    </div>


    <label for="dir">Séléctionnez le dossier ou ranger le fichier</label>
    <div class="row">
        <div class="p-45">
            <select name="dir" id="dir" required>
                <option value="">Séléctionnez un dossier</option>
                <?php foreach($dirs as $d){ ?>
                    <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="p-10">
            <label style="text-align: center; display: block; margin-top: 30px;" for="">Ou</label>
        </div>
        <div class="p-45">
            <input type="text" name="new-dir" placeholder="Ou entrez le nom d'un nouveau dossier">

        </div>
    </div>
    <input type="submit" value="Extraire">

    <iframe src="" frameborder="0" id="download" style="display: none;"></iframe>

    <a id="export" href="#null">Télécharger toutes les entreprises du dossier</a>


    <pre id="results"></pre>


</form>


<div class="bm-modal mdp">
    <div class="bm-modal--container">

        Se rendre sur la page suivante : <a target="_blank" href='https://www.linkedin.com/search/results/people/?facetNetwork=["F","S","O"]'>https://www.linkedin.com/search/results/people/?facetNetwork=["F","S","O"]</a>


        <p>Tableau des entreprises à scrapper :</p>
    <pre class="js">
    <span class="companies"></span>
    </pre>
    </div>
</div>


<script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="V2/assets/js/utils.js"></script>

<script type="text/javascript">



    var companies;
    var companies_error = [];
    var current_tab = '#simple';


    var interval;

    var $ = jQuery;




    $('#stop').on('click',function(){
        clearInterval(interval);
        showSuccess('stopped');
    });

    $('.bm-modal').on('click',function(e){


        if(!$(e.target).hasClass('bm-modal--container') && $(e.target).closest('.bm-modal--container').length == 0 ){
            $(this).fadeOut(350);
        }
    })


    $('#more').on('click',function(e){
        e.preventDefault();

        $('.more').toggle(200);
    })


    $('#file').on('change',function(){
        var reader = new FileReader();
        reader.onload = function () {
            companies = csvJSON(reader.result);

            console.log(companies);


            var a = [];

            $.each(companies,function(key,value){

                var val = extractFromCsv(value,['Company','company','Société','Entreprise']);

                if($.inArray(val,a) == -1){

                    a.push(val);
                }

            })


            $('#open-code')
                .show()
                .attr('href','linkedin-code.php?companies=' + encodeURIComponent(JSON.stringify(a)));


            $('#imported').html(companies.length);
        };


        if(!$(this)[0].files[0]){
            return;
        }

        var ext = $(this)[0].files[0].name.substr($(this)[0].files[0].name.lastIndexOf('.') + 1);


        if(ext != 'csv'){
            showError('not-csv');
            return;
        }

        // start reading the file. When it is done, calls the onload event defined above.
        reader.readAsBinaryString($(this)[0].files[0]);
    });



    $('.tab').on('click',function(e){

        e.preventDefault();


        $('.tabset').hide();
        $('.tab').removeClass('active');

        current_tab = $(this).attr('href');
        $(this).addClass('active');
        $(current_tab).show();

        if(current_tab == '#bulk'){
            $('#company').removeAttr('required');
        }else{
            $('#company').attr('required','required');
        }

    });

    $('#form').on('submit',function(e){

        e.preventDefault();
        if(current_tab == '#simple'){
            searchSimple();
        }else{
            searchBulk();
        }

    });



    function searchBulk()
    {
        var i = 0;
        var successed = 0;
        var errored = 0;
        var email = 0;
        var already = 0;
        var searched = 0;

        $('.error-bandeau').hide();
        $('.success-bandeau').hide();



        interval = setInterval(_search,_getSearchIntervalNumber());




        _search();

        function _search() {

            var params = {
                dir: get_dir(),
                scrap_verif : $('#scrap_verif').is(':checked'),
                societe_only : $('#societe_only').is(':checked'),
                srap_ceo : $('#srap_ceo').is(':checked'),
                re_scrap : $('#re_scrap').is(':checked'),
                api_hunter : $('#api_hunter').val(),
                api_hunter_email : $('#api_hunter_email').val(),
                search_engine : $('#search_engine').val(),
                siren : extractFromCsv(companies[i],['siren']),
                linkedin : extractFromCsv(companies[i],['linkedin']),
                first_name : extractFromCsv(companies[i],['first_name','prenom','Prénom','First Name']),
                last_name : extractFromCsv(companies[i],['last_name','nom','Nom','last Name']),
                company : extractFromCsv(companies[i],[,'company','Société','societe','Entreprise']),
                website : extractFromCsv(companies[i],['website','site','Site internet','domain']),
                full_name : extractFromCsv(companies[i],['full_name','Nom complet','Personne','Full Name']),
                position : extractFromCsv(companies[i],['position','Position','fonction','Fonction','Poste','title']),
                address : extractFromCsv(companies[i],['address','Adresse','Ville','Pays','adresse']),
                turnover : extractFromCsv(companies[i],['turnover','Chiffre d\'affaire','CA']),
                phone : extractFromCsv(companies[i],['phone','tel','téléphone','telephone']),
                email : extractFromCsv(companies[i],['email','E-mail','mail']),
            };


            if(params['company'] == ''){
                showError('csv-invalid');
                clearInterval(interval);
                return;
            }


            var query = $.param(params);

            $.get('V2/extract.php?' + query)
                .done(function(response) {
                    console.log(response);

                    if(response.data){
                        $.each(response.data,function(key,value){

                            if(value.error){
                                _displayError(value);
                            }else{
                                _displaySuccess(value)
                            }
                        })
                    }else{
                        _displaySuccess(response.responseJSON);
                    }



                })
                .fail(function(response){

                    if(response.responseJSON && response.responseJSON.data){
                        $.each(response.responseJSON.data,function(key,value){

                            _displayError(value);
                        })
                    }else{
                        if(response.responseJSON){
                            _displayError(response.responseJSON);

                        }else{
                            _displayError(response);

                        }

                    }

                })
                .always(function(){
                    $('.progress--content').width( (i/companies.length)*100 + '%');
                    $('#current').html(i+1);
                });


            i++;

            if(!companies[i]){

                console.log(companies_error);

                showSuccess('finished');

                clearInterval(interval)
            }

        }


        function _displaySuccess(su)
        {
            $('#results')
                .removeClass('error')
                .removeClass('success')
                .append('<span class="success">' + JSON.Pretty(su) + "</span>\n");
            successed++;
            $('#successed').html(successed);
        }


        function _displayError(er)
        {
            $('#results')
                .removeClass('error')
                .removeClass('success')
                .append('<span class="error">' + JSON.Pretty(er.error) + "</span>\n");

            errored++;

            switch (er.error_key){
                case 'email' :
                    email++;
                    $('#email').html(email);
                    break;
                case 'result':
                    searched++;
                    companies_error.push(er.company);
                    //  companies.push(companies[i-1]);
                    $('#searched').html(searched);
                    break;
                case 'already_done':
                    already++;
                    $('#already').html(already);
                    break;
                case 'quota' :
                    showError('quota');
                    clearInterval(interval);
                    break;
                case 'captcha' :
                    showError('captcha');
                    clearInterval(interval);
                    break;
                case 'maps-quota' :
                    showError('maps-quota');
                    clearInterval(interval);
                    break;
            }

            $('#errored').html(errored);
        }


    }





    function searchSimple()
    {
        $('.loading-container').show();

        var params = {
            dir: get_dir(),
            website : $('input[name="website"]').val(),
            company : $('input[name="name"]').val(),
            first_name : $('input[name="first_name"]').val(),
            last_name : $('input[name="last_name"]').val(),
            position : $('input[name="position"]').val(),
            scrap_linkedin : $('#scrap_linkedin').is(':checked'),
            societe_only : $('#societe_only').is(':checked'),
            re_scrap : $('#re_scrap').is(':checked'),
            api_hunter : $('#api_hunter').val(),
            search_engine : $('#search_engine').val(),
            api_hunter_email : $('#api_hunter_email').val(),
        };


        var query = $.param(params);




        var url = 'V2/extract.php?' + query;
        $.get(url).done(function(response) {
                console.log(response);
                $('.loading-container').hide();
                $('#results').
                    addClass('success')
                    .removeClass('error')
                    .html(JSON.Pretty(response));
            })
            .fail(function(response){

                if(response.responseJSON.error_key == 'quota'){
                    showQuotaError();
                }

                console.log(response);
                $('.loading-container').hide();
                $('#results')
                    .addClass('error')
                    .removeClass('succress')
                    .html(JSON.Pretty(response.responseJSON));
            });
    }


    function get_dir()
    {
        var dir = $('input[name="new-dir"]').val();

        if(dir == ''){
            dir = $('select[name="dir"]').val();
        }
        return dir;
    }





    function _getSearchIntervalNumber()
    {
        var interv = 10000;
        if(extractFromCsv(companies[0],['first_name','prenom','Prénom','First Name'])){
            interv -= 2000;
        } if(extractFromCsv(companies[0],['last_name','nom','Nom','last Name'])){
        interv -= 2000;

    } if(extractFromCsv(companies[0],['website','site','Site internet','domain'])){
        interv -= 2000;


    } if(extractFromCsv(companies[0],['full_name','Nom complet','Personne','Full Name'])){
        interv -= 2000;


    }

        return Math.max(1000,interv);

    }


</script>




<script>
    // export
    $('#export').on('click',function(e){

        e.preventDefault();

        var link = 'V2/export.php?export&dir=' + get_dir();

        $('#download').attr('src',link);
    });
</script>


<script>
    // Handle the dirs
    $('input[name="new-dir"]').on('input',function(){
        if($(this).val() == ''){
            $('#dir').attr('required','required');
        }else{
            $('#dir').removeAttr('required');
        }
    });
</script>


<script>
    // Search
    $('#company').on('input',function(){

        var val = $(this).val();

        if(val.length >= 4){
            $.get('V2/search.php?company=' + val).done(function(response) {
                $('.loading-container').hide();
                $('#results')
                    .removeClass('success')
                    .removeClass('error')
                    .html(JSON.Pretty(response));
            })
        }else{
            $('#results')
                .html('');
        }
    });
</script>

</body>
</html>