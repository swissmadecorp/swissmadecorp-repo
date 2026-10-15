// var isMobile = {
//     Android: function() {
//         return navigator.userAgent.match(/Android/i);
//     },
//     BlackBerry: function() {
//         return navigator.userAgent.match(/BlackBerry/i);
//     },
//     iOS: function() {
//         return navigator.userAgent.match(/iPhone|iPad|iPod/i);
//     },
//     Opera: function() {
//         return navigator.userAgent.match(/Opera Mini/i);
//     },
//     Windows: function() {
//         return navigator.userAgent.match(/IEMobile/i);
//     },
//     any: function() {
//         return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
//     }
// };

$.QueryString = (function(paramsArray) {
    let params = {};

    for (let i = 0; i < paramsArray.length; ++i)
    {
        let param = paramsArray[i]
            .split('=', 2);

        if (param.length !== 2)
            continue;

        params[param[0]] = decodeURIComponent(param[1].replace(/\+/g, " "));
    }

    return params;
})(window.location.search.substr(1).split('&'))


function removeURLParam(url, param) {
    var urlparts= url.split('?');
    if (urlparts.length>=2)
    {
        var prefix= encodeURIComponent(param)+'=';
        var pars= urlparts[1].split(/[&;]/g);
        
        for (var i=pars.length; i-- > 0;)
            if (pars[i].indexOf(prefix, 0)==0)
                pars.splice(i, 1);

        if (pars.length > 0)
            return urlparts[0]+'?'+pars.join('&');
        else
            return urlparts[0];
    }
    else
        return url;
}

const getDeviceType = () => {
    const ua = navigator.userAgent;
    if (/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i.test(ua)) {
      return "tablet";
    }
    if (
      /Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Kindle|Silk-Accelerated|(hpw|web)OS|Opera M(obi|ini)/.test(
        ua
      )
    ) {
      return "mobile";
    }
    return "desktop";
};

function isMobile() {
    if( /Android|webOS|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
        return true;
    } else {
        return false;
    }
}

function isTablet() {
    if( /Android|webOS|iPad|Opera Mini/i.test(navigator.userAgent) ) {
        return true;
    } else {
        return false;
    }
}

Number.prototype.formatMoney = function(c, d, t){
    var n = this, 
        c = isNaN(c = Math.abs(c)) ? 2 : c, 
        d = d == undefined ? "." : d, 
        t = t == undefined ? "," : t, 
        s = n < 0 ? "-" : "", 
        i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))), 
        j = (j = i.length) > 3 ? j % 3 : 0;

    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};

function selectText(element) {
    if (/INPUT|TEXTAREA/i.test(element[0].tagName)) {
        element[0].focus();
        if (element[0].setSelectionRange) {
        element[0].setSelectionRange(0, element[0].value.length);
        } else {
        element[0].select();
        }
        return;
    }
    
    if (window.getSelection) { // All browsers, except IE <=8
        window.getSelection().selectAllChildren(element[0]);
    } else if (document.body.createTextRange) { // IE <=8
        var range = document.body.createTextRange();
        range.moveToElementText(element[0]);
        range.select();
    }
}