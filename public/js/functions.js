//Generic
var STATUS_OK    = 'ok';
var STATUS_ERROR = 'error';

//Google Maps
var MAP;
var MAP_DEFAULT_LAT       = '-19.925370823375808';
var MAP_DEFAULT_LNG       = '-44.054517175';
var MAP_DEFAULT_ZOOM      = 6;
var MAP_SET_MARKER_TIME   = 200;
var MAP_INFO_WINDOW_ARRAY = [];
var MAP_MARKERS_ARRAY     = [];
var MAP_GEOCODER          = new google.maps.Geocoder();
var MAP_MARKER_TIME       = 200;
var MAP_CURRENT_INFOWINDOW= null;


//Facebook
var FB_APP_ID;
var FB_BUTTON;
var FB_USERINFO;

$(document).ready( function(){

    // var w=window,d=document,e=d.documentElement,g=d.getElementsByTagName('body')[0],x=w.innerWidth||e.clientWidth||g.clientWidth,y=w.innerHeight||e.clientHeight||g.clientHeight;

    // //Alinha o menu ao centro.
    // $('.brand').css('margin-left', (x/2)-(400/2)+'px' );

    //Inicializa o mapa
    start_google_maps();

    //Inicializa o facebeook connect.
    start_facebook();

    $('a.link-modal').live('click',function(){
        var $link = $(this);
        var href  = $link.attr('href');
        $link.attr('href', '#');
        //Prevent double click.
        if(href != '#')
        {
            openModalWithUrl(href);
            $link.attr('href', href);
            return false;
        }
    });

    $('.modal-container').on('hidden', function () {
       setHash('');
    });

    //Menu
    menuCategoryClick();
    menuSubcategoryClose();

    //openModalWithUrl( url );
    closeModalClick();

    //Selects
    select_category_change();

	//Forms
	validate_form_sign_up();
	validate_form_sign_in();
	validate_form_add_marker();
	validate_form_contact();
	validate_form_search();

	//User
    checkUserStatus();

    //Markers
    getMarkers();
    toggleMarkers();

    //Deep Links
    getDeepLinks();
});



/**
 * Inicializa o Mapa
 *
 */
function start_google_maps()
{
    var data = '';
    if($('#map').length === 0 )
        return;

    var mapOptions= {
        zoom: MAP_DEFAULT_ZOOM,
        center: new google.maps.LatLng( MAP_DEFAULT_LAT, MAP_DEFAULT_LNG ),
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        scrollwheel: true
    };
    MAP = new google.maps.Map( document.getElementById("map") , mapOptions);

}

function start_facebook()
{
    if($('#fb-root').length === 0 )
        return;

    FB_APP_ID = $('#fb-root').attr('data-fb-id');

    window.fbAsyncInit = function() {
        FB.init({ appId: FB_APP_ID,
            status: true,
            cookie: true,
            xfbml: true,
            oauth: true}
        );
    };

    (function() {
        var e = document.createElement('script'); e.async = true;
        e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
        document.getElementById('fb-root').appendChild(e);
    }());

    //Quando clickar no botão do facebook.
    $('#fb-auth').live('click',function(){
        fbLogin();
        return false;
    });
}

function fbLogin()
{	
	showLoader(true);
    FB.login(function(response) {
    if (response.authResponse) {
        FB.api('/me', function(info) {
            login(response, info);
        });

        } else {
            //user cancelled login or did not grant authorization
            showLoader(false);
        }
    }, {scope:'email,user_birthday,status_update,publish_stream,user_about_me'});
}

function fbUpdateButton(response) {

    FB_BUTTON    = $('#fb-auth');// document.getElementById('fb-auth');
    FB_USERINFO  = $('#user-info');// document.getElementById('user-info');

    if(!FB_BUTTON || !FB_USERINFO )
        return;

    FB.Event.subscribe('auth.statusChange', fbUpdateButton);

    if (response.authResponse) {
        //user is already logged in and connected
        FB.api('/me', function(info) {
            login(response, info);
        });

        FB_BUTTON.onclick = function() {
            FB.logout(function(response) {
                logout(response);
            });
        };
    }
}


function login(response, info){

    if (response.authResponse) {

        var data = '';

        data = data + 'facebook_access_token=' + response.authResponse.accessToken;
        data = data + '&name=' + info.name;
        data = data + '&email=' + info.email;
        data = data + '&facebook_id=' + info.id;
        data = data + '&form=facebook';

        $.ajax({
            type: 'POST',
            url: '/ajax/add-marker-step-2',
            data: data,
            success: function( data )
            {
                showLoader(false);

                data = jsonToObject(data);

                formFeedback( data.status_msg, data.status );

                if(data.status == STATUS_OK)
                {
                    checkUserStatus();

                    if( data.map_point )
                        loadAddMarkerStep3( data.map_point );
                }
            },
            error: function (request, status, error) {
                showLoader(false);
                formFeedback( 'Erro interno, tente novamente mais tarde.', STATUS_ERROR );
            }
        });
    }
}

function logout(response){
    FB_USERINFO.innerHTML                          =   "";
    document.getElementById('debug').innerHTML     =   "";
    document.getElementById('other').style.display =   "none";
    showLoader(false);
}

//stream publish method
function streamPublish(name, description, hrefTitle, hrefLink, userPrompt){
    showLoader(true);
    FB.ui(
    {
        method: 'stream.publish',
        message: '',
        attachment: {
            name: name,
            caption: '',
            description: (description),
            href: hrefLink
        },
        action_links: [
            { text: hrefTitle, href: hrefLink }
        ],
        user_prompt_message: userPrompt
    },
    function(response) {
        showLoader(false);
    });

}
function showStream(){
    FB.api('/me', function(response) {
        streamPublish(response.name, 'I like the articles of Thinkdiff.net', 'hrefTitle', 'http://thinkdiff.net', "Share thinkdiff.net");
    });
}

function share(){
    showLoader(true);
    var share = {
        method: 'stream.share',
        u: 'http://thinkdiff.net/'
    };

    FB.ui(share, function(response) {
        showLoader(false);
        console.log(response);
    });
}

function graphStreamPublish(){
    showLoader(true);

    FB.api('/me/feed', 'post',
        {
            message     : "I love thinkdiff.net for facebook app development tutorials",
            link        : 'http://ithinkdiff.net',
            picture     : 'http://thinkdiff.net/iphone/lucky7_ios.jpg',
            name        : 'iOS Apps & Games',
            description : 'Checkout iOS apps and games from iThinkdiff.net. I found some of them are just awesome!'

    },
    function(response) {
        showLoader(false);

        if (!response || response.error) {
            alert('Error occured');
        } else {
            alert('Post ID: ' + response.id);
        }
    });
}

function fqlQuery(){
    showLoader(true);

    FB.api('/me', function(response) {
        showLoader(false);

        //http://developers.facebook.com/docs/reference/fql/user/
        var query       =  FB.Data.query('select name, profile_url, sex, pic_small from user where uid={0}', response.id);
        query.wait(function(rows) {
           document.getElementById('debug').innerHTML =
             'FQL Information: '+  "<br />" +
             'Your name: '      +  rows[0].name                                                            + "<br />" +
             'Your Sex: '       +  (rows[0].sex !== undefined ? rows[0].sex : "")                          + "<br />" +
             'Your Profile: '   +  "<a href='" + rows[0].profile_url + "'>" + rows[0].profile_url + "</a>" + "<br />" +
             '<img src="'       +  rows[0].pic_small + '" alt="" />' + "<br />";
         });
    });
}

function setStatus(){
    showLoader(true);

    status1 = document.getElementById('status').value;
    FB.api(
      {
        method: 'status.set',
        status: status1
      },
      function(response) {
        if (response === 0){
            alert('Your facebook status not updated. Give Status Update Permission.');
        }
        else{
            alert('Your facebook status updated');
        }
        showLoader(false);
      }
    );
}

function showLoader(status){
    $loader = $('#loader');
    if($loader.length === 0 )
        return;


    if (status)
        $loader.show();
    else
       $loader.hide();
}

function select_category_change()
{
	$('#category', '#addmarker').live('change',function(){

		var selected_value = this.value;

		if(selected_value == 0)
			return;

		var info = 'parent_id=' + selected_value;

		var $select_sub_category  = $('#sub_category', '#addmarker');
		var $select_category 	  = $(this);
		var select_category_html  = $select_category.html();

		$select_category.html( $('<option>').val( 0 ).text( 'Carregando...' ) );
		$select_sub_category.html( $('<option>').val( 0 ).text( 'Carregando...' ) );

		$.ajax({
            type: 'POST',
            url: '/ajax/select-subcategory',
            data: info,
            success: function( data )
            {
            	data = jsonToObject(data);
            	if(data.status == STATUS_OK){

            		$select_sub_category.html('');
            		$select_sub_category.html( $('<option>').val( 0 ).text( 'Escolha...' ) );
            		$.each( data.sub_categories, function( index, value ){

            			$select_sub_category.append( $('<option>').val( value.id ).text( value.category ) );
            		});

            		$select_category.html( select_category_html );
            		$select_category.val(selected_value);

            	}else{
            		$select_sub_category.html('');
            		$select_sub_category.html( $('<option>').val( 0 ).text( 'Escolha...' ) );

            		$select_category.html( select_category_html );
            		$select_category.val(selected_value);
            	}

            },
            error: function (request, status, error) {
            	$select_sub_category.html('');
        		$select_sub_category.html( $('<option>').val( 0 ).text( 'Escolha...' ) );

        		$select_category.html( select_category_html );
        		$select_category.val(selected_value);
		    }
        });
	});
}


function validate_form_sign_up()
{
    $('#submit-sign-up','#signup').live("click", function() {
        $('#signup').validate({
            rules:{
                name:{
                    required: true,
                    minlength: 3
                },
                email: {
                    required: true,
                    email: true,
                    remote: 'ajax/check-email'
                },
                password: {
                    required: true
                },
                password_confirm:{
                    required: true,
                    equalTo: "#password"
                }
            },
            messages:{
                 name:{
                     required: "O campo nome é obrigatorio.",
                     minlength: "O campo nome deve conter mais de 3 caracteres."
                 },
                 email: {
                     required: "O campo email é obrigatorio.",
                     email: "Email inválido.",
                     remote: "Email já cadastrado."
                 },
                 password: {
                     required: "O campo senha é obrigatorio."
                 },
                 password_confirm:{
                     required: "O campo de confirmação de senha é obrigatorio.",
                     equalTo: "O campo senha e confirmação de senha devem ser iguais."
                 }
            },
            submitHandler: function( form ){
                var info = $( form ).serialize();
                showLoader(true);
                $.ajax({
                    type: 'POST',
                    url: '/ajax/add-marker-step-2/',
                    data: info+'&form=signup',
                    success: function( data )
                    {
                        showLoader(false);

                        data = jsonToObject(data);

                        formFeedback( data.status_msg, data.status );

                        if(data.status == STATUS_OK)
                        {
                            form.reset();

                            checkUserStatus();

                            if( data.map_point )
                                loadAddMarkerStep3( data.map_point );


                        }
                    },
                    error: function (request, status, error) {
                        showLoader(false);
                        formFeedback( 'Erro interno, tente novamente mais tarde.', STATUS_ERROR );
                    }
                });

                return false;
            }

        });
    });
}


function validate_form_sign_in()
{
    $('#submit-sign-in','#signin').live("click", function() {

        $('#signin').validate({
            rules:{
                email:{
                    required: true,
                    email: true
                },
                password: {
                    required: true
                }
            },
            messages:{
                 email:{
                     required: "O campo email é obrigatorio.",
                     email: "Email inválido."
                 },
                 password: {
                     required: "O campo senha é obrigatorio."
                 }
            },
            submitHandler: function( form ){

                showLoader(true);
                var info = $( form ).serialize();
                $.ajax({
                    type: 'POST',
                    url: '/ajax/add-marker-step-2/',
                    data: info+'&form=signin',
                    success: function( data )
                    {
                        showLoader(false);

                        data = jsonToObject(data);
                        formFeedback( data.status_msg, data.status );
                        if(data.status == STATUS_OK)
                        {
                            form.reset();

                            checkUserStatus();

                            if( data.map_point )
                                loadAddMarkerStep3( data.map_point );


                        }
                    },
                    error: function (request, status, error) {
                        showLoader(false);
                        formFeedback( 'Erro interno, tente novamente mais tarde.', STATUS_ERROR );
                    }
                });

                return false;
            }

        });
    });



}


function validate_form_add_marker()
{

    checkMarkerExists();
    checkTypeChange();

    $('#submit-add-marker','#addmarker').live("click", function() {


		$('#addmarker').validate({
			rules:{
				address:{ 		required: true  },
				neighborhood:{ 	required: true  },
				city:{ 			required: true  },
				state:{ 		required: true  },
				title:{ 		required: true  },
				type:{ 			required: true  },
				content:{ 		required: true  },
				category:{ 		required: true  },
				sub_category:{ 	required: true  }
			},
			messages:{
				address:{ 		required: 'O campo endereço é obrigatório.'  },
				neighborhood:{ 	required: 'O campo bairro é obrigatório.'  },
				city:{ 			required: 'O campo cidade é obrigatório.'  },
				state:{ 		required: 'Escolha o estado.'  },
				title:{ 		required: 'O campo título é obrigatório.'  },
				type:{ 			required: 'Escolha o tipo.'  },
				content:{ 		required: 'O campo conteúdo é obrigatório.'  },
				category:{ 		required: 'Escolha a categoria.'  },
				sub_category:{ 		required: 'Escolha a sub-categoria.'  }
			},
			submitHandler: function( form ){

				var type_validation = false;
                var content = $('#content', '#addmarker').val();
                var type = $('#type', '#addmarker').val();

                switch( type )
                {
                    //Link do Youtube
                    case '1':

                        if( !checkYoutubeURL( content )){
                            formFeedback( 'O conteúdo não é uma url do Youtube válida.', STATUS_ERROR );
                        }else{
                            type_validation = true;
                        }

                        break;

                    //Link de Imagem
                    case '2':
                        if( !checkImageURL( content )){
                            formFeedback( 'O conteúdo não é uma url de Imagem válida.', STATUS_ERROR );
                        }else{
                            type_validation = true;
                        }

                        break;

                    //Texto livre
                    case '3':
                        if( content.length === 0){
                            formFeedback( 'O conteúdo não contém texto.', STATUS_ERROR );
                        }else{
                            type_validation = true;
                        }

                        break;
                }



                if( type_validation )
                {
                    $('#form-feedback').fadeOut();
                    showLoader(true);
                    var info = $( form ).serialize();

                    $.ajax({
                        type: 'POST',
                        url: '/ajax/add-marker-step-1',
                        data: info+'&form=add_marker',
                        success: function( data )
                        {
                            showLoader(false);

                            data = jsonToObject(data);
                            formFeedback( data.status_msg, data.status );
                            if(data.status == STATUS_OK)
                            {
                                openModalWithUrl('/usuario/');
                            }
                        },
                        error: function (request, status, error) {
                            showLoader(false);
                            formFeedback( 'Erro interno, tente novamente mais tarde.', STATUS_ERROR );
                        }


                    }); //End ajax.

                }
                return false;
            }

        });
    });



}


function validate_form_contact()
{
    $('#submit-contact','#contact').live("click", function() {

        $('#contact').validate({
            rules:{
                name: { required: true },
                email:{ required: true, email: true },
                message:{ required: true }
            },
            messages:{
                name: { required: 'O campo nome é obrigatorio.' },
                email:{ required: 'O campo email é obrigatorio.', email: 'Email inválido.' },
                message:{ required: 'O campo mensagem é obrigatorio.' }
            },
            submitHandler: function( form ){

                showLoader(true);
                var info = $( form ).serialize();
                $.ajax({
                    type: 'POST',
                    url: '/ajax/contato',
                    data: info,
                    success: function( data )
                    {
                        showLoader(false);

                        data = jsonToObject(data);
                        formFeedback( data.status_msg, data.status );
                        if(data.status == STATUS_OK)
                        {
                            form.reset();
                        }
                    },
                    error: function (request, status, error) {
                        showLoader(false);
                        formFeedback( 'Erro interno, tente novamente mais tarde.', STATUS_ERROR );
                    }
                });

                return false;
            }

        });
    });



}


function validate_form_search()
{
    $('#submit-search','#search').live("click", function() {

        $('#search').validate({
            submitHandler: function( form ){

                var info = $( form ).serialize();

                globalFeedbackShow('Carregando...');

                $.ajax({
                    type: 'POST',
                    url: '/ajax/get-markers',
                    data: info,
                    success: function( data )
                    {
                        data = jsonToObject(data);
                        if(data.status == STATUS_OK)
                        {
                            var time = 0;
                            var bounds = new google.maps.LatLngBounds();

                            if(data.count > 0 )
                            {
                                $.each( MAP_MARKERS_ARRAY , function(index, marker ) {
                                    marker.setMap(null);
                                });

                                $.each(data.markers, function(key, value) {

                                    //time = MAP_MARKER_TIME*key;
                                    //Verifica os marcadores para dar zoom e exibir todos.
                                    bounds.extend(new google.maps.LatLng(value.latitude,value.longitude));

                                        setTimeout(function(){
                                            //$('span','#feedback').text('Carregando '+value.titulo);
                                           // $('#feedback').show();
                                            setMarker(key, value);
                                        }, time);
                                });
                                MAP.fitBounds(bounds);
                                globalFeedbackHide();

                                //$('#search').append('<a href="/">Mostrar todos</a>');
                            }
                        }

                    },
                    error: function (request, status, error) {
                        formFeedback( 'Erro interno, tente novamente mais tarde.', STATUS_ERROR );
                        globalFeedbackHide();
                    }
                });
                return false;
            }

        });
    });



}




function checkTypeChange()
{
    $('#type','#addmarker').live("change",function(){

        $select = $(this);
        $content = $('#content', '#addmarker');

        var type = $select.val();
        switch(type)
        {
            case '1':
                $content.css({
                    'height': '26px',
                    'resize': 'none'
                });
                $('label[for="content"]', '#addmarker').text('Link do youtube');
                break;
            case '2':
                $content.css({
                    'height': '26px',
                    'resize': 'none'
                });
                $('label[for="content"]', '#addmarker').text('Link da imagem');
                break;

			case '3':
			default:
				$content.css('height', '55px');
				$('label[for="content"]', '#addmarker').text('Texto');
				break;

        }
        $content.focus();
    });

}

function checkImageURL( url )
{
    //http://stackoverflow.com/questions/9581448/validate-image-url
    var result = false,
        img;
    try {
        img = document.createElement("img");
        img.src = url;

    } catch(err){
        result = false;
    }

    if(img.height > 0) {
        result = true;   //image exists
    }

    return result;

}

function checkYoutubeURL( url )
{
    if(  youtubeCodeParser( url ) )
        return true;
    else
        return false;
}

function youtubeCodeParser( url )
{

    //http://stackoverflow.com/questions/3452546/javascript-regex-how-to-get-youtube-video-id-from-url
    var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/;
    var match = url.match(regExp);
    if (match&&match[7].length==11){
        return match[7];
    }else{
       return false;
    }
}


function checkMarkerExists()
{
    $('input, select', '#addmarker').live('blur',function(){

        var map_preview_url = 'http://maps.google.com/maps/api/staticmap?center=-17.930178,-43.790845&zoom=3&size=960x200&sensor=false';
        var latitude        = 0;
        var longitude       = 0;

        var $address        = $('#address','#addmarker');
        var $neighborhood   = $('#neighborhood','#addmarker');
        var $city           = $('#city','#addmarker');
        var $state          = $('#state','#addmarker');
        var $type           = $('#type','#addmarker');
        var $content        = $('#content','#addmarker');

        var full_address = [$address.val(), $neighborhood.val(), $city.val(), $state.val()].join(' ');

        MAP_GEOCODER.geocode( { 'address': full_address}, function(results, status){

          if (status == google.maps.GeocoderStatus.OK) {

              latitude  = results[0].geometry.location.lat();
              longitude = results[0].geometry.location.lng();


              map_preview_url = 'http://maps.google.com/maps/api/staticmap?center='+latitude+','+longitude+'&markers='+latitude+','+longitude+'&zoom=15&size=960x200&sensor=false';



          }

          //Exibe o preview.
          $("#map-preview").attr('src', map_preview_url);
          $('#latitude', '#addmarker').val( latitude );
          $('#longitude', '#addmarker').val( longitude );

        });




    });

}

function  openModalWithUrl( url )
{
    globalFeedbackShow('Carregando...');
    $('.modal-content').load(url, function(){
        $('.modal-container').show();

        if($(this).find('[class^="form"]').hasClass('form-overlay')){
            $(this).addClass('wide');
        }
        globalFeedbackHide();
    });
}

function menuCategoryClick()
{
	$('a', '#menu-category').live('click',function(){

		globalFeedbackShow('Carregando...');
		var category_id  = this.id.replace('category_', '');
		$('#subcategories').load( '/index/subcategories/category/'+category_id, function(){
			$('#subcategories').fadeIn();

			globalFeedbackHide();
		});
		return false;
    });
}

function menuSubcategoryClose()
{
	$('.close-subcategories').live('click', function(){
		$('#subcategories').fadeOut();
		return false;
	});
}

function closeModalClick()
{
    $('.close-modal').live('click',function(){
        $('.modal-container').fadeOut();

        setHash('');
        return false;
    });
}


function hideModal( modal_id )
{
    $(modal_id).fadeOut(1000, function(){
        $(modal_id).modal('hide');
    });
}

function goToAddMarkerStep3()
{
    $.ajax({
        type: 'POST',
        url: '/ajax/add-marker-step-2/',
        data: 'form=none',
        success: function( data )
        {
            showLoader(false);

            data = jsonToObject(data);
            formFeedback( data.status_msg, data.status );
            if(data.status == STATUS_OK)
            {
                if( data.map_point )
                    loadAddMarkerStep3( data.map_point );
            }
        },
        error: function (request, status, error) {
            showLoader(false);
            formFeedback( 'Erro interno, tente novamente mais tarde.', STATUS_ERROR );
        }
    });
}

function loadAddMarkerStep3( map_point_id )
{


    openModalWithUrl('/mapa/'+map_point_id+'/?type=key');
    getMarkers();
}


function checkUserStatus()
{
	$user_status = $('#user-status');

	$user_status.html('Aguarde...');

    $user_status.fadeIn();

    $.ajax({
        type: 'POST',
        url: '/ajax/user-status',
        success: function( data )
        {
            data = jsonToObject(data);
            if( data.status == STATUS_OK )
            {
                $user_status.html( 'Olá '+data.user.name+' <a href="/index/logout" class="logout" title="Sair    :(">Sair</a>');
                return true;
            }
            else
            {
                $user_status.fadeOut();
                return false;
            }

        },
        error: function (request, status, error) {
        	 $user_status.fadeOut();
        }
    });
}

function formFeedback( msg, type )
{
    type = ( type == STATUS_OK ) ? 'success' : 'error';

    $feedback = $('#form-feedback');
    $feedback.removeClass('alert-success').removeClass('alert-error');
    $feedback.addClass( 'alert-'+type ).text(msg).show();
}

function jsonToObject(json)
{
    return eval('('+json+')');
}

function getMarkers()
{
    if($('#map').length === 0 )
        return;

    var data = null;
    globalFeedbackShow('Carregando...');
    $.ajax({
        type: 'POST',
        url: '/ajax/get-markers',
        data: data,
        success: function( response ){

            var response = jsonToObject(response);
            if(response.status == STATUS_OK)
            {
                var time = 0;
                var bounds = new google.maps.LatLngBounds();

                if(response.count > 0 )
                {
                    $.each(response.markers, function(key, value) {

                        //time = MAP_MARKER_TIME*key;
                        //Verifica os marcadores para dar zoom e exibir todos.
                        bounds.extend(new google.maps.LatLng(value.latitude,value.longitude));

                            setTimeout(function(){
                                //$('span','#feedback').text('Carregando '+value.titulo);
                               // $('#feedback').show();
                                setMarker(key, value);
                            }, time);
                    });
                    MAP.fitBounds(bounds);
                    globalFeedbackHide();

                    /*
                    //Retira o texto de feedback e coloca o zoom pra exibir tudo.
                    if(response.count > 1 )
                    {
                        setTimeout(function(){
                                MAP.fitBounds(bounds);
                        }, time*1.5 );

                    }else{

                        MAP.fitBounds(bounds);
                        MAP.setZoom(15);
                    }
                */



                }else{
                    globalFeedbackHide();
                }

            }else{
                  //  $('span','#feedback').text('Erro ao carregar locais.');
                globalFeedbackHide();
            }

            $('#categories').fadeIn();

        }//End success;
    });
}

/*
* Set Marker
* Coloca os marcadores no mapa.
* @param key = chave do local no loop de locais.
* @param value = json com os dados do local.
* @author Emerson Carvalho <emerson.broga@gmail.com>
* @since 03/11/2011
*
*/
function setMarker(key, value)
{
	//http://www.cycloloco.com/shadowmaker/shadowmaker.htm
    var shadow = new google.maps.MarkerImage('/images/shadow.png',
            new google.maps.Size(59.0, 43.0),
            new google.maps.Point(0, 0),
            new google.maps.Point(18.0, 21.0)
    );

    var shape = {
            coord: [1, 1, 1, 37, 43, 59, 43 , 1],
            type: 'poly'
    };

    value.icon_file = '/uploads/marker/' +value.icon_file;

    var image = new google.maps.MarkerImage(value.icon_file,
            new google.maps.Size(37.0, 43.0),
            new google.maps.Point(0, 0),
            new google.maps.Point(18.0, 21.0)
    );

    var LatLng = new google.maps.LatLng(value.latitude, value.longitude);
    var marker = new google.maps.Marker({
        position: LatLng,
        map: MAP,
        shadow: shadow,
        icon: image,
        shape: shape,
        title: value.category,
        zIndex: key
    });

    //Adiciona alguns parametros extras ao marcador.
    marker.setValues({
        category : value.category_id,
        latitude: value.latitude,
        longitude: value.longitude,
        visible: true
    });


    MAP_MARKERS_ARRAY.push(marker);

    var infoWindow = new google.maps.InfoWindow({
        map: MAP,
        content: '<div class="modal-header"><h4>'+value.title+'</h4></div><div class="modal-body">'+value.content+'<br><a class="deep-link" href="'+value.slug+'">ver</a></div>',
        position: new google.maps.LatLng(value.latitude, value.longitude),
        shadowStyle: 1,
        padding: 0,
        backgroundColor: '#ffffff',
        borderRadius: 4,
        borderWidth: 1,
        borderColor: '#2c2c2c',
        disableAutoPan: true,
        hideCloseButton: true,
        arrowSize: 15,
        arrowPosition: 50,
        arrowStyle: 0,
        backgroundClassName: 'phoney',
        maxWidth: 800,
        minWidth: 300,
        maxHeight: 400,
        minHeight: 250,
        width:800,
        height:400
    });
    MAP_INFO_WINDOW_ARRAY.push(infoWindow);
    infoWindow.close();

    google.maps.event.addListener(marker, 'click', function() {

        //openModalWithUrl( value.slug );
        setHash(  value.slug );


        /*
        if(MAP_CURRENT_INFOWINDOW)
            MAP_CURRENT_INFOWINDOW.close();

        infoWindow.open(MAP,marker);
        MAP_CURRENT_INFOWINDOW = infoWindow;

        var lat = parseFloat(marker.latitude)+parseFloat(0.01);

        LatLng = new google.maps.LatLng(lat, marker.longitude);
        MAP.setCenter(LatLng);

        */
        return false;
    });
}




function toggleMarkers()
{
	$('#filter-subcategory').live('click', function(){

		var $checkboxes = $('li input:checkbox:checked', '#choose-subcategory');

		//Array onde serão armazenados os pontos Inativos.
        var disabled_markers = [];

        //Array onde serão armazenados os pontos Ativos.
        var enabled_markers = [];

        //Define se é pra ativar ou desativar.
        var enabled;

        //Id do esporte Selectionado
       // var category_id = $(this).attr('id').replace('category_','');

        //Verifica quais pontos estão Ativos.
        $checkboxes.each( function(index, value ){
        	enabled_markers.push( value.id.replace('subcategory-', ''));
        });

        //Variavel com as Latitudes e longitudes.
        var bounds = new google.maps.LatLngBounds();

        //Variavel para a contagem de Marcadores Visiveis no mapa.
        var count_visible = 0;

        //Loop em cada um dos marcadores.
        $.each( MAP_MARKERS_ARRAY , function(index, marker ) {

        	if( in_array( marker.category, enabled_markers ) )
        	{
        		marker.setVisible( true );
        	}
        	else
        	{
        		marker.setVisible( false );
        	}
        });
            return false;
	});



	$('#filter-subcategory-show-all').live('click', function(){
		$.each( MAP_MARKERS_ARRAY , function(index, marker ) {

			marker.setVisible( true );
        });
	});

}

function setHash( hash )
{
    window.location.hash = hash;
}

function getDeepLinks()
{
    var newhash = '';
    //$mainContent = $('#main-content');

    $('.deep-link').live('click', function(){
        //$('.modal-header').delegate('.deep-link', 'click', function(){
        setHash( $(this).attr('href') );
        return false;
    });

    $(window).bind('hashchange', function(){

        newhash = window.location.hash.substring(1);
        if(newhash)
        {
            /*
            globalFeedbackShow('Carregando...');
            $('#global-modal').load(newhash, function(){
                $('#global-modal').modal({
                    backdrop: 'static', show:true
                });
                if(MAP_CURRENT_INFOWINDOW)
                    MAP_CURRENT_INFOWINDOW.close();

                globalFeedbackHide();
            });
            */

            openModalWithUrl( newhash );

        }
    });

    $(window).trigger('hashchange');
}


function globalFeedbackShow( text )
{
    $('#global-feedback').show();
    $('span', '#global-feedback').text(text);

}

function globalFeedbackHide()
{
    $('#global-feedback').hide();
    $('span', '#global-feedback').text('');
}


function in_array (needle, haystack, argStrict) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: vlado houba
    // +   input by: Billy
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: in_array('van', ['Kevin', 'van', 'Zonneveld']);
    // *     returns 1: true
    // *     example 2: in_array('vlado', {0: 'Kevin', vlado: 'van', 1: 'Zonneveld'});
    // *     returns 2: false
    // *     example 3: in_array(1, ['1', '2', '3']);
    // *     returns 3: true
    // *     example 3: in_array(1, ['1', '2', '3'], false);
    // *     returns 3: true
    // *     example 4: in_array(1, ['1', '2', '3'], true);
    // *     returns 4: false
    var key = '',
        strict = !! argStrict;

    if (strict) {
        for (key in haystack) {
            if (haystack[key] === needle) {
                return true;
            }
        }
    } else {
        for (key in haystack) {
            if (haystack[key] == needle) {
                return true;
            }
        }
    }

    return false;
}


