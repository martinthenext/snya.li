<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="google-site-verification" content="rdruda66FbY1tGpCJdSJcN-31odAxCloUqs6nWfkw_s" />
        <meta name="google-site-verification" content="OldNbJax8LFNl64eq6_-iCDFHHHFJ6zJNZ5m3D1_ASw" />
        <link rel="shortcut icon" type="image/x-icon" href="<?= Yii::app()->request->hostInfo ?>/favicon.ico" />
        <meta property="og:title" content="<?= $this->pageTitle ?> | СНЯ.ЛИ" />
        <meta property="og:type" content="website"/>
        <meta property="og:image" content="<?= Yii::app()->request->hostInfo ?>/images/logo.png"/>
        <meta property="og:site_name" content="snya.li"/>
        <meta property="og:description" content="<?= $this->pageDescription ?>"/>
        <link rel="canonical" href="<?= Yii::app()->request->hostInfo . Yii::app()->request->requestUri ?>"/>
        <meta property="og:url" content="<?= Yii::app()->request->hostInfo . Yii::app()->request->requestUri ?>"/>
        <title><?= $this->pageTitle ?></title>
        <?php if (!empty($this->pageDescription)): ?>
            <meta name="description" content="<?= $this->pageDescription ?>" />
        <?php endif; ?>
        <?php if (!empty($this->pageKeywords)): ?>
            <meta name="keywords" content="<?= $this->pageKeywords ?>" />
        <?php endif; ?>
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <script type="text/javascript" src="//yastatic.net/share/share.js" charset="utf-8"></script>
    </head>

    <body role="document">
        <!-- Fixed navbar -->
        <?php $this->widget('application.widgets.Navbar', $this->navbarOptions); ?>
        <div class="container theme-showcase" role="main">

            <?= $content ?>

            <a href="https://twitter.com/snyali_snyali" class="twitter-follow-button" data-show-count="false">Follow @snyali_snyali</a>
            <script>!function (d, s, id) {
                    var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location) ? 'http' : 'https';
                    if (!d.getElementById(id)) {
                        js = d.createElement(s);
                        js.id = id;
                        js.src = p + '://platform.twitter.com/widgets.js';
                        fjs.parentNode.insertBefore(js, fjs);
                    }
                }(document, 'script', 'twitter-wjs');</script>

        </div> <!-- /container -->

        <div class="modal fade" id="select-city" tabindex="-1" role="dialog" aria-labelledby="select-city-label" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="select-city-label">Выбрать город</h4>
                    </div>
                    <div class="modal-body">
                        <?php
                        $cities = array();

                        foreach ($this->cities as $city) {
                            $cities[strtoupper(mb_substr($city->title, 0, 1))][] = $city;
                        }
                        ?>
                        <?php foreach ($cities as $lit => $cityGroup): ?>
                            <div style="max-width: 150px;">
                                <strong><?= $lit ?></strong><br />
                                <?php foreach ($cityGroup as $city): ?>
                                    <a class="city-link" href="<?= Yii::app()->createAbsoluteUrl("items/CityChange", array('city' => $city->link)) ?>">
                                        <?= $city->title ?>
                                    </a><br />
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                        <div style="clear:  both;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Yandex.Metrika counter -->
        <script type="text/javascript">
            (function (d, w, c) {
                (w[c] = w[c] || []).push(function () {
                    try {
                        w.yaCounter18742765 = new Ya.Metrika({
                            id: 18742765,
                            clickmap: true,
                            trackLinks: true,
                            accurateTrackBounce: true,
                            webvisor: true
                        });
                    } catch (e) {
                    }
                });

                var n = d.getElementsByTagName("script")[0],
                        s = d.createElement("script"),
                        f = function () {
                            n.parentNode.insertBefore(s, n);
                        };
                s.type = "text/javascript";
                s.async = true;
                s.src = "https://mc.yandex.ru/metrika/watch.js";

                if (w.opera == "[object Opera]") {
                    d.addEventListener("DOMContentLoaded", f, false);
                } else {
                    f();
                }
            })(document, window, "yandex_metrika_callbacks");
        </script>
        <noscript><div><img src="https://mc.yandex.ru/watch/18742765" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
        <!-- /Yandex.Metrika counter -->
    </body>
</html>
<script type="text/javascript">
    $(document).ready(function () {
        $('#myLightbox').lightbox();
    });
</script>
<script type="text/javascript">
    /*<![CDATA[*/
    (function ($)
    {

        var $win = $(window);


        if (!(window.parent == window)) {
            var el = document.createElement("script");
            el.type = "text/javascript";
            el.src = "//vk.com/js/api/xd_connection.js?2";
            el.async = false;
            $("head").append(el);
            var interval = setInterval(function () {
                if (typeof (VK) == 'object') {
                    clearInterval(interval);
                    VK.init(function () {
                        setInterval(newSizeWindow, 100);
                        function newSizeWindow() {
                            var h = $('body').height();
                            if (h < 827) {
                                h = 827;
                            }
                            VK.callMethod("resizeWindow", 760, h);

                        }

                        VK.addCallback("onScroll", function (scrollTop, windowHeight) {
                            if (scrollTop + windowHeight >= $marker.offset().top) {
                                $('#showMore').trigger('click');
                            }
                        });

                        VK.callMethod("scrollSubscribe", true);

                    });
                }
            }, 1000);
        }


        var $marker = $('#showMore');

        $win.scroll(function () {
            if ($win.scrollTop() + $win.height() + 100 >= $marker.offset().top) {
                $('#showMore').trigger('click');
            }
        });

    })(jQuery);
    /*]]>*/
</script>
