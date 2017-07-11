/**
 *
 * Extact values from a csv line
 *
 *
 * @param list  object the list you want to extract data fom
 * @param keys  array  The keys you're looking for
 * @returns {*}
 */
function extractFromCsv(list,keys)
{
    var result = '';

    $.each(list,function(key,value){
        if($.inArray(key,keys) != -1 && typeof value == 'string'){
            result = value.replace(/"/,'');
            return;
        }
    });

    return result;
}






/**
 *
 * Transform  csv string into a JSON Object
 *
 *
 * @param csv
 * @returns {Array}
 *
 function csvJSON(csv){

    if(csv.includes('Ã©')){
        csv = decodeURIComponent(escape(csv));
    }

    var lines=csv.split("\n");

    delimiter = getCSVDelimiter(lines[0])

    var result = [];

    var headers=lines[0].split(delimiter);

    for(var i=1;i<lines.length;i++){

        if(lines[i] == ''){
            continue;
        }

        var obj = {};


        var currentline=lines[i].split(delimiter);


        for(var j=0;j<headers.length;j++){
            obj[headers[j]] = currentline[j] ? currentline[j].replace(/"/gi,'') : '';
        }

        result.push(obj);

    }

    return result; //JavaScript object
}*/



/**
 *
 * Transform  csv string into a JSON Object
 *
 * @from https://jsfiddle.net/BlueUrbanSky/26uww1et/
 *
 * @param csv
 * @returns {Array}
 */

function csvJSON(csv) {

    if(csv.includes('Ã©')){
        csv = decodeURIComponent(escape(csv));
    }


    var i;
    var j;
    //this could be changed to replace with just "'"
    var input = csv.replace(/\"\"/g, encodeURIComponent('"'));
    //split on " to create an odds in quotes
    var quotesAndValues = input.split(/\"/g);
    var escapedInput;

    var delimiter = getCSVDelimiter(csv);

    var quotesAndValuesLength = quotesAndValues.length;
    //encode the odd positions as these should be treated as one value
    //and need to ignore ,
    for (i = 1; i < quotesAndValuesLength; i = i + 2) {
        quotesAndValues[i] = encodeURIComponent(quotesAndValues[i]);
    }
    escapedInput = quotesAndValues.join("");

    var lines = escapedInput.split(/\r\n|\n/g);

    var result = [];
    //split index 0 at , to get headers
    var headers = lines[0].split(new RegExp(delimiter,'g'));

    for (i = 1; i < lines.length; i++) {

        var obj = {};
        //splitat , to get values
        var currentline = lines[i].split(new RegExp(delimiter,'g'));
        for (j = 0; j < headers.length; j++) {
            //double decode
            //first: decodes the quoted values , % etc
            //second: decodes the double quotes that were escaped at the start as %22 (%2522)
            //this may not be performant


            try{
                obj[headers[j]] = decodeURIComponent(decodeURIComponent(currentline[j]));
            }
            catch (URIError){
                obj[headers[j]] = currentline[j];
            }
        }

        result.push(obj);

    }

    //return result; //JavaScript object
    return result; //JSON
}




/**
 *
 * Get the CSV Delimiter from a CSV line
 *
 * @param chunk
 * @returns {*}
 */
function getCSVDelimiter(chunk) {

    var items = [',', ';', '\t', '|'];


    var ignoreString = false
    var itemCount = {}
    var maxValue = 0
    var maxChar
    var currValue
    items.forEach(function (item) {
        itemCount[item] = 0
    })
    for (var i = 0; i < chunk.length; i++) {
        if (chunk[i] === '"') ignoreString = !ignoreString
        else if (!ignoreString && chunk[i] in itemCount) {
            currValue = ++itemCount[chunk[i]]
            if (currValue > maxValue) {
                maxValue = currValue
                maxChar = chunk[i]
            }
        }
    }
    return maxChar
}


/**
 *
 * Show the error message
 *
 * @param key
 */
function showError(key)
{
    $('.error-'+ key).show();
}


/**
 *
 * Show the Success message
 *
 * @param key
 */
function showSuccess(key)
{
    $('.success-'+ key).show();
}


/**
 *
 * Serialise a jquery Object
 *
 * @returns {{}}
 */
$.fn.serializeObject = function() {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name]) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};





/**
 *
 * Display a beautiful json string
 *
 * @param data
 * @returns {*}
 * @constructor
 */
JSON.Pretty = function(data) {

    return JSON.stringify(data, null, '\t')
}

