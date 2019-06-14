function card(id) {
    var sub = cards[id];
    cardShow(id, cardActive[id]);
    $('#exampleModalCenter').modal({});
    var out = '';
    for (var i = 0; i < sub.length; i++) {
        var e = sub[i];
        if (!e[0]) {
            btn = 'btn-success';
        } else {
            btn = 'btn-warning';
        }
        out += '<a href="javascript:cardShow(\'' + id + '\', \'' + e[2] + '\')" class="btn ' + btn + '">' + e[1] + ' <small>(' + e[2] + ')</small></a>';
    }
    $('#modalNav').html(out);
}

function cardShow(id, img) {
    cardActive[id] = img;
    $('#modalImage')
        .attr('src', 'card/' + img + '.jpeg');
    $('#' + id)
        .css('background-image', 'url(\'card/' + img + '.jpeg\')')
        .addClass('card');
}
