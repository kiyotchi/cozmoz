<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script type="text/javascript">
  if ( window.opener ) {
    window.opener.handleTwitterLoggedIn(<?php echo (int)$result;?>);
    window.close();
  } else {
    window.onload = function() {
      document.body.innerHTML = 'リダイレクト先が見つかりませんでした。';
     };
  }
</script>
</head>
<body>redirecting...</body>
</html>