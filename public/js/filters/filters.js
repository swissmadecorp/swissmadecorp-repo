var ms='',vars = [];

//ms = getUrlVars();
function getUrlVars(rep,elem) {
    var hash='',b_found=-1;
    var address = window.location.href;

    s = address.indexOf("&_=");
    if (s!=-1)
        address = address.substr(0,s)
    
    s = address.indexOf("&status=ajax");
    if (s!=-1)
        address = address.substr(0,s)

    s = address.indexOf("query=");
    if (s!=-1)
        address = address.substr(0,s)

    var hashes = address.slice(address.indexOf('?') + 1).split('&');

    if (rep == 'clear'){
        var indx='';
        
        $.each (hashes, function (i,v) {
            if (v.indexOf('p=')>-1){
                indx = v;
            }
        })

        url = address.split('?')[0];
        return url;
    }
        
    if (hashes[0].indexOf('http')!=-1) {
        hashes.splice(0,1);
    }

    if (elem != undefined) {
        str = elem.parent('li').attr('data-original');
        f = $.inArray(str,hashes);
        if (f != -1)
            hashes.splice(f,1);
        
    }

    if (rep != undefined) {    
        $.each (hashes, function (i,v) {
            test1 = hashes[i].split('=')[0];
            test2 = rep.split('=')[0];
            if (test1 == test2) {
                b_found = i;
            }
        })

        if (b_found != -1)
            hashes[b_found] = rep;
        else {
            hashes.push(rep);
        }
            

        $.each (hashes, function (i,v) {
            hash += v+'&';
        })

        return '?'+hash.substr(0,hash.length-1);
    
    } else {
        if (typeof hashes[0] != 'undefined' && hashes[0]) {
           
            $('#filter-group ul').html('');
            $.each (hashes, function (i,v) {
                hash += v+'&';
                m = v.replace(/%20/g, " ");
                if (m.indexOf('p=')>-1) {
                    $('#filter-group ul').prepend("<li data-original='"+m+"'>"+strRep(m)+"</li>");
                } else if (m.indexOf('status=')>-1) {
                    $('#filter-group ul').prepend("<li data-original='"+m+"'>"+strRep(m.replace('_'," "))+"<button class='remove_filter'><i class='fa fa-times' aria-hidden='true'></i></button></li>");
                } else if (m.indexOf('page=')==-1) 
                    $('#filter-group ul').prepend("<li data-original='"+m+"'>"+strRep(m)+"<button class='remove_filter'><i class='fa fa-times' aria-hidden='true'></i></button></li>");
            })

            if ($('.clear-all').length == 0 && hashes.length>1)
                $('.filter-list').append("<a class='btn btn-primary clear-all' style='padding: 4px;' href=''>Clear Filters</a>");

            if (elem != undefined) {
                return '?'+hash.substr(0,hash.length-1);
            }

        } else {
            return window.location.href.split('?')[0];
        }
    }
}

function strRep(str) {
    spl = str.split("=");
    
    spl1 = spl[0].charAt(0).toUpperCase() + spl[0].slice(1);
    spl2 = spl[1].charAt(0).toUpperCase() + spl[1].slice(1);

    if (spl1 == 'P')
        spl1 = 'Search';

    return spl1+': '+spl2;
}

function redirectWithFilter(ms) {
    url = window.location.href.split('?')[0];
    url = url.replace(/search|page/g,'');
    ms = '?'+ms.substr(ms.indexOf('&')+1);
    if (window.location.href.indexOf('watches')==-1)
        document.location.href = url+'watches/'+ms.replace(/\s/g,'-').toLowerCase();
    else document.location.href = url+ms.replace(/\s/g,'-').toLowerCase();
}

$('._condition a').click ( function () {
    ms = getUrlVars($(this).attr('data-filter'));
    if (window.location.href.indexOf('search')>-1 || window.location.href.indexOf('page')>-1){
        redirectWithFilter(ms);
        return;
    }
    document.location.href = ms.replace(' ','-').toLowerCase();
})

$('._facecolor a').click ( function () {
    ms = getUrlVars($(this).attr('data-filter'));
    if (window.location.href.indexOf('search')>-1 || window.location.href.indexOf('page')>-1){
        redirectWithFilter(ms);
        return;
    }
    document.location.href = ms.replace(/\s/g,'-').toLowerCase();
})

$('._status a').click ( function (e) {
    e.preventDefault();
    ms = getUrlVars($(this).attr('data-filter'));
    if (window.location.href.indexOf('search')>-1 || window.location.href.indexOf('page')>-1){
        redirectWithFilter(ms);
        return;
    }
    document.location.href = ms.replace(' ','-').toLowerCase();
})

$('.clear-all').click ( function (e) {
    e.preventDefault();
    ms = getUrlVars('clear');
    if (ms.indexOf('search')>-1){
        document.location.href = 'watches';
        return 
    }
    document.location.href = ms.toLowerCase();
})

$('.remove_filter').click( function () {
    ms = getUrlVars($(this).attr('data-filter'), $(this));
    document.location.href = ms.toLowerCase();
})

$('.price button').click( function (e) {
    e.preventDefault();
    builder = 'price='+$('input[name="pfrom"]').val()+'-'+$('input[name="pto"]').val();
    ms = getUrlVars(builder);
    
    document.location.href = ms.toLowerCase();
})

