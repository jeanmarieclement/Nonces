
# OOP ORIENTED IMPLEMTATION wp_nonce #


This class as be made as a test, it's not to use in production environment.
The class have the basic function for creating and verifying a nonce

it's already declarated as $jmcNonce:

## $jmcNonce->Create() ##
### Description ###
Generates and returns a nonce. The nonce is generated based on the current time, the $action argument, and the current user ID.

### Usage ###
```
<?php $jmcNonce->Create ( $action ); ?>
```

### Parameters ###
$action
(string/int) (optional) Action name. Should give the context to what is taking place. Optional but recommended.
Default: -1
Return Values
(string) 
The one use form token.

### Example ###
In this simple example, we create an nonce and use it as one of the GET query parameters in a URL for a link. 
When the user clicks the link they are directed to a page where a certain action will be performed (for example, a post might be deleted). On the target page the nonce is verified to insure that the request was valid (this user really clicked the link and really wants to perform this action).
```
<?php
// Create an nonce for a link.
// We pass it as a GET parameter.
// The target page will perform some action based on the 'do_something' parameter.
$nonce = $jmcNonce->Create( 'my-nonce' );
?>
<a href='myplugin.php?do_something=some_action&_wpnonce=<?php echo $nonce; ?>'>Do some action</a>

<?php 
// This code would go in the target page.
// We need to verify the nonce.
$nonce = $_REQUEST['_wpnonce'];
if ( ! $jmcNonce->Verify( $nonce, 'my-nonce' ) ) {
    // This nonce is not valid.
    die( 'Security check' ); 
} else {
    // The nonce was valid.
    // Do stuff here.
}
?>
```

## $jmcNonce->CreateURL() ##
### Description ###
Retrieve URL with nonce added to URL query.

The returned result is escaped for display.

### Usage ###
```
<?php $jmcNonce->CreateURL( $actionurl, $action, $name ); ?>
```

### Parameters ###
$actionurl
(string) (required) URL to add nonce action
Default: None
$action
(string) (optional) nonce action name
Default: -1
$name
(string) (optional, since 3.6) nonce name
Default: _jmcNonce
Return Values
(string) 
URL with nonce action added.

### Examples ###
```
function my_plugin_do_something () {
?>
<h2><?php esc_html_e('My Plugin Admin Screen', 'my-plugin-textdomain');?></h2>
<p>
    <a href="<?php print $jmcNonce->CreateURL(admin_url('options.php?page=my_plugin_settings'), 'doing_something', 'my_nonce');?>"
        class="button button-primary"><?php esc_html_e('Do Something!', 'my-plugin-textdomain');?></a>
    <span class="description"><?php esc_html_e('This button does something interesting.', 'my-plugin-textdomain');?></span>
</p>
<?php
}
```
Then check nonce validity
```
if (isset($_GET['my_nonce']) || $jmcNonce->Verify($_GET['my_nonce'], 'doing_something')) {
    // do something
} else {
    // display an error or the form again
}
```    

## $jmcNonce->CreateField() ##
### Description ###
Retrieves or displays the nonce hidden form field.

The nonce field is used to validate that the contents of the form request came from the current site and not somewhere else. A nonce does not offer absolute protection, but should protect against most cases. It is very important to use nonce fields in forms.

The $action and $name arguments are optional, but if you want to have a better security, it is strongly suggested to give those two arguments. It is easier to just call the function without any arguments, because the nonce security method does not require them, but since crackers know what the default is, it will not be difficult for them to find a way around your nonce and cause damage.

The nonce field name will be whatever $name value you gave, and the field value will be the value created using the $jmcNonce->Create() function.

### Usage ###
```
<?php $jmcNonce->CreateField( $action, $name, $referer, $echo ) ?>
```

### Parameters ###
$action
(string) (optional) Action name. Should give the context to what is taking place. Optional but recommended.
Default: -1
$name
(string) (optional) Nonce name. This is the name of the nonce hidden form field to be created. Once the form is submitted, you can access the generated nonce via $_POST[$name].
Default: '_jmcNonce'
$referer
(boolean) (optional) Whether also the referer hidden form field should be created with the wp_referer_field() function.
Default: true
$echo
(boolean) (optional) Whether to display or return the nonce hidden form field, and also the referer hidden form field if the $referer argument is set to true.
Default: true
Return Values
(string) 
The nonce hidden form field, optionally followed by the referer hidden form field if the $referer argument is set to true.

### Examples ###
```
<form method="post">
   <!-- some inputs here ... -->
   <?php $jmcNonce->CreateField( 'name_of_my_action', 'name_of_nonce_field' ); ?>
</form>
```

Then in the page where it is being submitted to, you may verify it using the $jmcNonce->Verify() function. 
Notice that you have to manually retrieve the nonce (from the $_POST array in this example), and the name of the action is the 2nd parameter instead of the first:
```
<?php

if ( 
    ! isset( $_POST['name_of_nonce_field'] ) || ! $jmcNonce->Verify( $_POST['name_of_nonce_field'], 'name_of_my_action' ) 
) {

   print 'Sorry, your nonce did not verify.';
   exit;

} else {

   // process form data
}
```

## $jmcNonce->Verify() ##
### Description ###
Verify that a nonce is correct and unexpired with the respect to a specified action. The function is used to verify the nonce sent in the current request usually accessed by the $_REQUEST PHP variable.

Nonces should never be relied on for authentication or authorization, access control. Protect your functions using current_user_can(), always assume Nonces can be compromised.

### Usage ###

<?php $jmcNonce->Verify( $nonce, $action ); ?>


### Parameters ###
$nonce
(string) (required) Nonce to verify.
Default: None
$action
(string/int) (optional) Action name. Should give the context to what is taking place and be the same when the nonce was created.
Default: -1
Return Values
(boolean/integer) 
Boolean false if the nonce is invalid. Otherwise, returns an integer with the value of:
1 – if the nonce has been generated in the past 12 hours or less.
2 – if the nonce was generated between 12 and 24 hours ago.

### Example ###
Verify an nonce created with $jmcNonce->Create():
```
<?php

// Create an nonce, and add it as a query var in a link to perform an action.
$nonce = $jmcNonce->Create( 'my-nonce' );

echo "<a href='myplugin.php?_wpnonce={$nonce}'>Save Something</a>";

?>

.....

<?php 

// In our file that handles the request, verify the nonce.

$nonce = $_REQUEST['_wpnonce'];

if ( ! $jmcNonce->Verify( $nonce, 'my-nonce' ) ) {

     die( 'Security check' ); 

} else {

     // Do stuff here.
}

?>
```
You may also decide to take different actions based on the age of the nonce:
```
<?php

$nonce = $jmcNonce->Verify( $nonce, 'my-nonce' );

switch ( $nonce ) {

    case 1:
        echo 'Nonce is less than 12 hours old';
    break;

    case 2:
        echo 'Nonce is between 12 and 24 hours old';
    break;

    default:
        exit( 'Nonce is invalid' );
}

?>
```