<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script type="text/javascript">
  if ( window.opener ) {
    window.opener.handleSocialLoggedIn(<?php echo (int)$result;?>);
    window.close();
  } else {
    location.href = '<?php echo base_url();?>';
  }
</script>
</head>
<body>redirecting...</body>
</html>