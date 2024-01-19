<?php session_start();?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .carousel-inner img {
            width: 100%;
            height: auto;
        }
    </style>
    <title>О нас</title>
</head>
<body>
<?php include('header_user.php'); ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6">
            <h2>О нас</h2>
                <p>
                    Добро пожаловать в наше приложение, посвященное обеспечению быстрого и удобного поиска ветеринарных клиник в городе Москва.
                    Мы предоставляем возможность легко находить ближайшие к вам медицинские учреждения для обеспечения ваших питомцев качественной ветеринарной помощью.
                </p>
                <p>
                    Наше приложение также предлагает уникальную возможность связи с опытными ветеринарами в различных областях. Вы можете получать консультации и рекомендации от квалифицированных специалистов прямо из дома. Это особенно удобно в ситуациях, когда требуется совет по уходу, кормлению или другим вопросам заботы о животных.
                </p>
            </div>
            <div class="col-md-6">
                <div id="carouselExample" class="carousel slide" data-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img src="static/images/image1.png" class="d-block w-100" alt="Фотография 1">
                        </div>
                        <div class="carousel-item">
                            <img src="static/images/image2.jpg" class="d-block w-100" alt="Фотография 2">
                        </div>
                        <div class="carousel-item">
                            <img src="static/images/image3.jpg" class="d-block w-100" alt="Фотография 3">
                        </div>
                    </div>
                    <a class="carousel-control-prev" href="#carouselExample" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Предыдущая</span>
                    </a>
                    <a class="carousel-control-next" href="#carouselExample" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Следующая</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.2.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
   
    <footer>
    <?php include('footer.html');?>
    </footer>
</body>
</html>
