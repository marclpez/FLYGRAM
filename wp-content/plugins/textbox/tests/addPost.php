<?php
/* Config */
$token = '123123';

$postContent = <<<POST
<p>
    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas neque risus, sollicitudin molestie volutpat eu, rhoncus eget lorem. Fusce vel mollis ipsum. Maecenas feugiat sapien sit amet ipsum accumsan, lacinia porttitor erat semper. Quisque blandit posuere porta. Etiam sollicitudin fringilla felis, id venenatis quam mattis id. In bibendum enim nec purus rhoncus, at luctus risus imperdiet. Quisque condimentum dui nisl, sodales pellentesque tellus molestie at. Proin eget diam risus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Cras vitae massa suscipit, fermentum sapien venenatis, eleifend felis. Donec feugiat mi id iaculis varius. Aliquam dictum eros placerat, rutrum risus at, condimentum felis. Donec commodo vulputate mi vitae bibendum. Ut a malesuada ante. Sed aliquet velit at finibus vestibulum. Suspendisse potenti.
</p>

<p>
    <img src="http://www.osmais.com/wallpapers/201302/cachoeira-floresta-wallpaper.jpg" />
</p>

<p>
    Nam sagittis sem et odio sollicitudin condimentum. Donec venenatis at lorem sed vestibulum. Curabitur lacus arcu, efficitur ut aliquet id, ornare at sem. Nunc eget odio arcu. Aliquam in tortor a est gravida rhoncus. Pellentesque magna turpis, porta non vestibulum in, gravida eu justo. Morbi varius dictum ante id laoreet. Nunc sed pharetra purus. Morbi elementum magna mi, non sollicitudin massa bibendum in. Ut urna eros, sagittis et nisi id, viverra dictum risus. Donec non leo et neque facilisis ullamcorper. Duis ultricies leo at urna pharetra tincidunt. Morbi euismod diam neque, sit amet sollicitudin velit posuere nec. Aenean risus risus, bibendum sed velit eu, eleifend consectetur ligula. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Vestibulum auctor condimentum neque, at egestas leo elementum vitae.
</p>

<p>
    <img src="http://wallpaper.ultradownloads.com.br/112141_Papel-de-Parede-Cachoeira--112141_1600x1200.jpg" />
</p>

<p>
    Nam sagittis sem et odio sollicitudin condimentum. Donec venenatis at lorem sed vestibulum. Curabitur lacus arcu, efficitur ut aliquet id, ornare at sem. Nunc eget odio arcu. Aliquam in tortor a est gravida rhoncus. Pellentesque magna turpis, porta non vestibulum in, gravida eu justo. Morbi varius dictum ante id laoreet. Nunc sed pharetra purus. Morbi elementum magna mi, non sollicitudin massa bibendum in. Ut urna eros, sagittis et nisi id, viverra dictum risus. Donec non leo et neque facilisis ullamcorper. Duis ultricies leo at urna pharetra tincidunt. Morbi euismod diam neque, sit amet sollicitudin velit posuere nec. Aenean risus risus, bibendum sed velit eu, eleifend consectetur ligula. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Vestibulum auctor condimentum neque, at egestas leo elementum vitae.
</p>
POST;

$fields = array(
	'action' => 'textbox_add_post',
    'token' => $token,

    'post_author' => 3, // Id do usuario

    'post_name' => 'belas-cachoeiras', // Slug (parte da url amigável que vem após o host na url)
    'post_title' => 'Belas Cachoeiras', // Título do post
    'post_content' => $postContent, // Conteúdo do post
    'post_excerpt' => false, // Resumo do post

    'post_status' => 'draft', // [ 'draft' | 'publish' | 'pending'| 'future' | 'private' | custom registered status ] Default é 'draft'

    'post_date' => false, // Format [Y-m-d H:i:s]

    'comment_status' => false, // 'open' | 'closed' / Se não passar o padrão será o padrão configurado no Wordpress

    'post_category' => '6, 9, 11', // id das categorias separado por virgula
    'tags_input' => 'tag1, tag2, tag3' // string tags separado por virgula
);

/* Do not touch */
preg_match( '/(.*)\/wp-content/', $_SERVER['REQUEST_URI'], $wordpressDir );
$wordpressDir = $wordpressDir[1] ? $wordpressDir[1] : '';
$url = 'http://' . $_SERVER['SERVER_NAME'] . $wordpressDir . '/wp-admin/admin-ajax.php';

//url-ify the data for the POST
$arStringFields = array();
foreach( $fields as $key => $value ) {
	$value = urlencode( $value );
	$arStringFields[] = "{$key}={$value}";
}
$stringFields = implode('&', $arStringFields);


//open connection
$curl = curl_init();

//set the url, number of POST vars, POST data
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_POST, count($fields));
curl_setopt($curl, CURLOPT_POSTFIELDS, $stringFields);

//execute post
$result = curl_exec($curl);
print $result;