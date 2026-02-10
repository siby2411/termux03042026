<!DOCTYPE html>
<html>
<head>
 <title>Système de notification</title>
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
 <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
 <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
 <script type="text/javascript" src="script.js"></script>
</head>
<body>
<p>
 <div class="container" style="background-color: yellow;">
  <nav class="navbar navbar-default">
   <div class="container-fluid">
    <div class="navbar-header">
     <a class="navbar-brand" href="#">Test de notification</a>
    </div>
    <ul class="nav navbar-nav navbar-right">
     <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="label label-pill label-danger count" style="border-radius:9px;"></span> <span class="glyphicon glyphicon-bell" style="font-size:19px;"></span></a>
      <ul class="dropdown-menu"></ul>
     </li>
    </ul>
   </div>
  </nav>
  <br />
  <form method="post" id="notify_form">
   <div class="form-group">
    <label>Saisissez le sujet</label>
    <input type="text" name="sujet" id="sujet" class="form-control">
   </div>
   <div class="form-group">
    <label>Saisissez un message</label>
    <textarea name="message" id="message" class="form-control" rows="6"></textarea>
   </div>
   <div class="form-group">
    <input type="submit" name="post" id="post" class="btn btn-primary" value="Soumettre" />
   </div>
  </form>
 </div>
</body>
</html>