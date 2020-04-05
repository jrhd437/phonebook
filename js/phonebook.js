var hash = location.hash;
hash = hash.replace("#", "");

if (typeof (Storage) === "undefined") {
    alert("Your browser is old and does not work with modern tools. Consider switching to a modern, 'ever-green' browser like the latest version of Microsoft EDGE, Google Chrome or Apple Safari.");
}

if (localStorage.watchlist != undefined && localStorage.watchlist != '') {
    var watchlist = JSON.parse(localStorage.watchlist);
} else {
    var watchlist = [];
}

new Vue({
    el: '#app',
    data: {
        contacts: [],
        keyword: hash,
        relationship: 'ALL',
        error: '',
    },
    mounted: function () {
        var self = this;
    },
    methods: {
        contactSearch: function () {
            var self = this;
            console.log("http://127.0.0.1/phonebook/api/contact_finder.php/fetch/"+self.relationship+"/" + self.keyword);
            $.getJSON("http://127.0.0.1/phonebook/api/contact_finder.php/fetch/"+self.relationship+"/" + self.keyword, function (data) {
                if(data.success == false){
                    self.error = data.description;
                    self.contacts = [];
                } else {
                    self.contacts = data.results;
                }
                
            });
        },
    },
})