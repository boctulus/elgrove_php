const base_url = getSiteRoot();
const login_page = base_url + 'login';  

//console.log(base_url + 'login');

function checkpoint()
{
	if (typeof login_page === 'undefined' || typeof localStorage === 'undefined'){
		console.log('Error');
		return;
	}

	const expired = ((localStorage.getItem('exp')!=null) && ((localStorage.getItem('exp')*1000) - (new Date()).getTime())<0);
	
	if (expired)
		console.log('expired');

	if ((localStorage.getItem('access_token') == null) || expired){
		if (localStorage.getItem('refresh_token')){
			renew();	
		}else{
			window.location = login_page; 
		} 
			
	}		 
}

function register(){
	//console.log('here');

	var obj ={};

	if ($('#password').val() != $('#password_confirmation').val()){
		$('#registerError').text('Contraseñas no coinciden');
		return;
	}else $('#registerError').text('');

	obj.username = $('#username').val();
	obj.email = $('#email').val();	
	obj.password = $('#password').val();
	obj.firstname = $('#firstname').val();	
	obj.lastname = $('#lastname').val();

	$.ajax({
		type : "POST",
		url: "/auth/register",
		data : JSON.stringify(obj),
		dataType: 'json',
		success : function(res) {

			var data = res.data;

			if (typeof data.access_token != 'undefined'){
				console.log('Token recibido');
				localStorage.setItem('access_token',data.access_token);
				localStorage.setItem('refresh_token',data.refresh_token);
				localStorage.setItem('expires_in',data.expires_in);
				localStorage.setItem('exp', parseInt((new Date).getTime() / 1000) + data.expires_in);
				console.log('Tokens obtenidos',data);
				window.location = base_url; 
			}else{		
				$('#registerError').text('Error desconcido');
				console.log(data);
			}
		},
		error: function(xhr, status, error){
			console.log(JSON.parse(xhr.responseText));
			$('#registerError').text(JSON.parse(xhr.responseText).error);
		}
	});

	return false;
}

function login(){
	var obj ={};
	
	if ($('#email_username').val().match(/@/) != null)
		obj.email    = $('#email_username').val();	
	else
		obj.username = $('#email_username').val();
	
	obj.password = $('#password').val();
	
	// get form data
	//obj = this.serializeObject();

	$.ajax({
		type: "POST",
		url: '/auth/login',
		data: JSON.stringify(obj),
		dataType: 'json',
		success: function(res){

			var data = res.data;

			if (typeof data.access_token != 'undefined' && typeof data.refresh_token != 'undefined'){
				localStorage.setItem('access_token',data.access_token);
				localStorage.setItem('refresh_token',data.refresh_token);
				localStorage.setItem('expires_in',data.expires_in);
				localStorage.setItem('exp', parseInt((new Date).getTime() / 1000) + data.expires_in);
				console.log('Tokens obtenidos');
				window.location = base_url;
			}else{	
				console.log('Error (success)',data);	
				$('#loginError').text(data.responseJSON.error);
			}
		},
		error: function(xhr){
			console.log('Error (error)',xhr);
			$('#loginError').text('Error de autenticación!!!');
		}
	});		

	return false;
}


function logout(){
	localStorage.removeItem('access_token');
	localStorage.removeItem('refresh_token');
	window.location.href = login_page;
}

function renew(){
	console.log('Renewing token at ...'+(new Date()).toString());

	$.ajax({
		type: "POST",
		url: '/auth/token',
		dataType: 'json',
		headers: {"Authorization": 'Bearer ' + localStorage.getItem('refresh_token')}, 
		success: function(res){
			var data = res.data;

			if (typeof data.access_token != 'undefined'){
				localStorage.setItem('access_token',data.access_token);
				localStorage.setItem('expires_in',data.expires_in);
				localStorage.setItem('exp', parseInt((new Date).getTime() / 1000) + data.expires_in);
				
				//console.log(data.access_token);
			}else{
				console.log('Error en la renovación del token');
				////////window.location = login_page;
			}
		},
		error: function(data){
			console.log('Error en la renovación del token!!!!!!!!!!!!');
			console.log(data);
			/////////window.location = login_page;
		}
	});		
}

function rememberme(){
	var obj ={};
	
	obj.email = $('#email').val();	

	// get form data
	//obj = this.serializeObject();

	$('#remembermeError').text('');

	$.ajax({
		type: "POST",
		url: '/login/rememberme_process',
		data: JSON.stringify(obj),
		dataType: 'text', 
		success: function(res){
			window.location.replace('/login/rememeberme_mail_sent/' + window.btoa(obj.email));
		},
		error: function(xhr, status, error){
			console.log('ERROR');
			console.log(xhr.responseJSON);

			if (xhr.responseJSON && xhr.responseJSON.error)
				$('#remembermeError').text(xhr.responseJSON.error);
			else{
				$('#remembermeError').text('Error - intente más tarde');
				console.log(xhr);
				console.log(status);
				console.log(error);
			}	
				
		}
	});		

	return false;
}

function update_pass()
{
	if ($('#password').val() != $('#password_confirmation').val()){
		$('#passChangeError').text('Contraseñas no coinciden');
		return;
	}else 
		$('#passChangeError').text('');

	var obj = {};
	
	obj.password = $('#password').val();
	var token = window.location.pathname.split('/')[3];

	$.ajax({
		type : "POST",
		url: "/login/change_pass_process",
		headers: {"Authorization": 'Bearer ' + token},
		data : JSON.stringify(obj),
		dataType: 'json',
		success : function(res) {
			var data = res.data;

			if (data && data.access_token){
				console.log('Token recibido');
				localStorage.setItem('access_token',data.access_token);
				localStorage.setItem('refresh_token',data.refresh_token);
				localStorage.setItem('expires_in',data.expires_in);
				localStorage.setItem('exp', parseInt((new Date).getTime() / 1000) + data.expires_in);
				console.log('Tokens obtenidos',data);
				window.location = base_url; 
			}else{		
				$('#passChangeError').text('Error desconcido');
				console.log(res);
			}
		},
		error: function(xhr, status, error){
			console.log(xhr);
			console.log(status);
			console.log(error);
			$('#passChangeError').text(JSON.parse(xhr.responseText).error);
		}
	});

	return false;
}