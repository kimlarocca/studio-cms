<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="styles.css"/>
<title>Wellness On The Green | Register Online</title>
</head>
<body>
<h1>Wellness On The Green: Online Registration</h1>
<div class="twd_container">
  <h2>Step 1: Choose a Date</h2>
  <p><strong style="color:#f00;">PLEASE NOTE: </strong>We don't currently have the ability for class card students to reserve spots online. Please feel free to text <strong>862.485.1233</strong> with your full name and the class, date and time to reserve your spot!</p>
 <form action="register-online2.php" method="get">
 <input type="text" id="datePicked" name="datePicked"><br />
 <input name="Continue" type="submit" value="Continue" />
 </form>
  
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script> 
<script type="text/javascript" src="datePicker/picker.js"></script> 
<script>
/**
 * pick a date
 */
$('#datePicked').pickadate({
  onOpen: function() {
    scrollIntoView( this.$node )
  },
  format: 'yyyy-mm-dd'
})
function scrollIntoView( $node ) {
  $('html,body').animate({
      scrollTop: ~~$node.offset().top - 60
  })
}
</script>
</body>
</html>