MarketingExtras = (function () {
    var url = '';
    var xmlhttp = new XMLHttpRequest();

    function MarketingExtras(staticUrl, apiUrl)
    {
        url = staticUrl;
        xmlhttp.onload = onLoad;
        xmlhttp.open("GET", apiUrl, true);
        xmlhttp.send();
    }

    function onLoad(){
        if (xmlhttp.readyState === XMLHttpRequest.DONE) {
            var el = document.querySelector('.marketing-extras .iframe-container iframe');
            if (xmlhttp.status === 200) {
                url = JSON.parse(xmlhttp.responseText);
            }
            el.setAttribute('src', url);
        }
    }

    return MarketingExtras;
}());
