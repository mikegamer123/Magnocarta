<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Styles -->
    <style>
        .title {
            font-size: 30px;
        }

        .description {
            font-size: 20px;
        }
        .recommendations, .error{
            display: none;
        }
        .loader {
            width: 60px;
            display: none;
        }
        .loaderDiv{
            height: 60px;
        }
        .loader-wheel {
            animation: spin 1s infinite linear;
            border: 2px solid rgba(108, 122, 137, 1);
            border-left: 4px solid #000;
            border-radius: 50%;
            height: 50px;
            margin-bottom: 10px;
            width: 50px;
        }

        .loader-text {
            color: #000;
            font-family: arial, sans-serif;
        }

        .loader-text:after {
            content: 'Loading';
            animation: load 2s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes load {
            0% {
                content: 'Loading';
            }
            33% {
                content: 'Loading.';
            }
            67% {
                content: 'Loading..';
            }
            100% {
                content: 'Loading...';
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
          integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"
            integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>

</head>
<body>
<div class="container">
    <div class="row mt-5">
        <div class="col-12 mb-3">
            <h3 class="text-center">Hello I'm Magnocarta, your digital librarian<br>
                How can I help you?
            </h3>
        </div>
        <div class="col-3 d-none d-md-block"></div>
        <div class="col-md-6 col-12">
            <div class="p-1 bg-light rounded rounded-pill shadow-sm mb-4">
                <div class="input-group">
                    <input type="search" id="searchInput" placeholder="What're you searching for?"
                           aria-describedby="button-addon1"
                           class="form-control border-0 bg-light">
                    <div class="input-group-append">
                        <button id="searchBtn" class="btn">
                            <i class="fa fa-search"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-3 d-none d-md-block"></div>
    </div>
    <div class="row errorDiv">
        <div class="col-12 error text-danger text-center">
            There seems to be an error in your query. Please try again or write another question.
        </div>
    </div>
    <div class="row">
        <div class="col-12 loaderDiv">
            <div class="loader m-auto">
                <div class="loader-wheel"></div>
                <div class="loader-text"></div>
            </div>
        </div>
        <div class="recommendations">

        </div>
    </div>
    <script>
        $(document).ready(function () {
            $("#searchBtn").on("click", function () {
                $(".loader").fadeIn();
                $(".error").hide();
                let input = $("#searchInput").val();
                let url = "{{url()->current()}}/api/bookSearch?input=" + encodeURIComponent(input);

                $.get(url, function (data) {
                    // Handle the response data
                    let json = $.parseJSON(data);
                    let htmlContent = '';
                    if(json == null || json.error){
                        $(".loader").fadeOut();
                        $(".error").fadeIn();
                    }
                    else {
                        let recommendations = json.recommendations;
                        let container = $(".recommendations");
                        recommendations.forEach(function (recommendation) {
                            // Generate the HTML content for each book
                            let bookHtml = recommendation.bookName === "" ? "" : `
                           <div class= "bookDiv row my-3">
                            <div class="col-3">
                              <img src="https://pictures.abebooks.com/isbn/${recommendation.bookISBN.replace('-','')}-uk-300.jpg" width="100%" alt="Book Cover" onerror="this.onerror=null; this.style.display='none';"/>
                            </div>
                            <div class="col-9">
                              <p class="title">${recommendation.bookName}</p>
                              <p class="description">${recommendation.bookDescription}</p>
                            </div>
                           </div>
                          `;
                            htmlContent += bookHtml;
                        });
                        $(".loader").fadeOut();
                        if(htmlContent !== "") {
                            container.html(htmlContent);
                        }
                        else{
                            htmlContent+= `
                             <div class="col-12">
                              <p class="title">No books can be found for the search of "${input}"</p>
                            </div>
                            `;
                            container.html(htmlContent);
                        }
                        container.fadeIn();
                    }
                });
            });
        });
    </script>
</body>
</html>
