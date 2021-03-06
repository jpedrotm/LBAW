<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{$title}</title>

    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/tagsInput.css">
    <link href="https://fonts.googleapis.com/css?family=Oswald" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.4/Chart.min.js"></script>

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

    <script src="https://use.fontawesome.com/71cda9ffac.js"></script>

    <script src="//cdnjs.cloudflare.com/ajax/libs/pagedown/1.0/Markdown.Converter.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/pagedown/1.0/Markdown.Editor.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/pagedown/1.0/Markdown.Sanitizer.js"></script>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.11.1/typeahead.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput-typeahead.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/twbs-pagination/1.4.1/jquery.twbsPagination.min.js"></script>
    
    <script src="../../js/tagsInput.js"></script>
    <script src="../../js/scripts.js"></script>
   
    <link rel="stylesheet" href="//cdn.rawgit.com/balpha/pagedown/master/demo/browser/demo.css"/>



</head>

<body class="{$title}">

<nav class="navbar navbar-default">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="../../pages/home/home.php">
                <img alt="Brand" src="../../resources/images/logo.png">
                <img alt="Brand" src="../../resources/images/askabit.png">
            </a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <div class="col-sm-3 col-md-3 col-md-offset-3">
                <form action="../../pages/home/search.php" method="get" class="navbar-form" role="search">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search" name="search">
                        <div class="input-group-btn">
                            <button class="btn btn-default" type="submit"><i class="glyphicon glyphicon-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>

            {if isset($smarty.session.id)}
                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown" style="background-color: #10354b">
                        <a href="#" class="dropdown-toggle" style="background-color: #10354b" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <img class="img-circle" alt="user" src="{$image}">
                            <span>{$smarty.session.username}</span>
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="../../pages/member/profile.php?id={$smarty.session.id}"><i class="glyphicon glyphicon-user"></i> Perfil</a></li>
                            <li><a href="../../pages/topic/createTopic.php"><i class="glyphicon glyphicon-plus"></i> Novo tópico</a></li>
                            {if $smarty.session.usertype == 'admin'}
                            <li><a href="../../pages/admin/admin.php"><i class="glyphicon glyphicon-eye-open"></i> Admin </a></li>
                            {/if}
                            <li role="presentation" class="divider"></li>
                            <li><a href="../auth/logout.php"><i class="glyphicon glyphicon-off"></i> Sair</a></li>
                        </ul>
                    </li>
                </ul>
            {else}
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="../auth/login.php"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>
                </ul>
            {/if}
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>
