<?php
require_once('lib/recaptchalib.php');
require_once('lib/config.php');

// Klucze dla zamekkamieniec.iq.pl
$publickey = "6LeVOOISAAAAAN7K9ba-jEgAqcr-kJlyjdypOPnb";
$privatekey = "6LeVOOISAAAAAH_VgokmXmN8nPhfasx7JaDHV95H";
// Klucze dla js-test.iq.pl
//$publickey = "6LfzX90SAAAAAEL64jU_DDOjXGa_9uV5xki5BhBT";
//$privatekey = "6LfzX90SAAAAACsHpGiNpgoE4E-mVSsgSnaLRFxt";
// the response from reCAPTCHA
$resp = null;
// the error code from reCAPTCHA, if any
$error = null;
// tytuł, styl i treść okienka dialogowego
$messagetitle = null;
$messageerror = 0;
$messagetext = null;

if (!empty($_POST)) {
    if (get_magic_quotes_gpc() == true)
        ini_set('magic_quotes_gpc', 'off');

    error_reporting(E_STRICT); // opcje: E_ALL, E_STRICT

    ini_set('display_error', "0"); // opcje: 1 -> wyświetlanie błędów włączone, 0 -> wyłączone

    date_default_timezone_set('Europe/Warsaw');

    require_once('lib/class.phpmailer.php');

    define('SCRIPT', '1');

    if (SCRIPT == 0)
        die('Skrypt zablokowany. Stała SCRIPT ma wartość 0');


    $name = strip_tags($_POST['name']); // Filtracja danych za pomocą strip_tags() - usuwa znaki HTML
    $email = strip_tags($_POST['email']);
    $message = strip_tags($_POST['message']);

    if (!is_array($_POST)) {
        $messageerror = 1;
        $messagetitle = "Naruszenie zasad bezpieczeństwa";
        $messagetext = "$_POST nie jest tablicą. Ze względów bezpieczeństwa - wysłanie wiadomości zostało zablokowane.";
    } elseif (!is_string($name) || !is_string($email) || !is_string($message)) {
        $messageerror = 1;
        $messagetitle = "Naruszenie zasad bezpieczeństwa";
        $messagetext = "Przekazane dane nie są ciągiem znaków. Ze względów bezpieczeństwa - wysłanie wiadomości zostało zablokowane.";
    } elseif (empty($name) || empty($email) || empty($message)) {
        $messagetitle = "UWAGA!";
        $messagetext = 'Wypełnij wszystkie pola.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $messagetitle = "UWAGA!";
        $messagetext = 'Wpisz poprawny adres e-mail.';
    } elseif (strlen($message) < 5) {
        $messagetitle = "UWAGA!";
        $messagetext = 'Wiadomość nie może być krótsza niż 5 znaków.';
    }
//	elseif($_SERVER['HTTP_HOST'] !== 'zamekkamieniec.iq.pl'){
//		$messageerror = 1;
//		$messagetitle = "Naruszenie zasad bezpieczeństwa";
//		$messagetext = "Próba wysłania wiadomości ze strony innej niż zamekkamieniec.iq.pl. Ze względów bezpieczeństwa - wysłanie wiadomości zostało zablokowane.";
//	}
    elseif ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $messageerror = 1;
        $messagetitle = "Naruszenie zasad bezpieczeństwa";
        $messagetext = "Wiadomość musi zostać przesłana metodą POST. Ze względów bezpieczeństwa - wysłanie wiadomości zostało zablokowane.";
    } else {

        // was there a reCAPTCHA response?
        if ($_POST["recaptcha_response_field"]) {
            $resp = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

            if ($resp->is_valid) {
                $address_ip = $_SERVER['REMOTE_ADDR'];
                $user_data = $_SERVER['HTTP_USER_AGENT'];

                $mail = new PHPMailer();

                $mail->IsSMTP();   // telling the class to use SMTP
                $mail->SMTPDebug = 0;  // enables SMTP debug information (for testing)
                // 1 = errors and messages
                // 2 = messages only
                $mail->SMTPAuth = true; // enable SMTP authentication
                $mail->SMTPSecure = "ssl"; // sets the prefix to the server
                $mail->Host = MAIL_HOST; // sets the SMTP server
                $mail->Port = 465; // set the SMTP port
                $mail->Username = MAIL_USER; // SMTP account username
                $mail->Password = MAIL_PASS; // SMTP account password
                $mail->CharSet = "UTF-8";

                $mail->SetFrom(MAIL_FROM, 'formularz kontaktowy');

                $mail->Subject = "Wiadomość wysłana za pomocą formularza kontaktowego ze strony zamekkamianiec.iq.pl";

                $mail->AltBody = "Aby przeczytać wiadomość użyj programu zgodnego z HTML!"; // optional, comment out and test

                $mail->MsgHTML("
							<p>Nadawca: <strong>$name</strong> </p>
							<p>E-mail: <strong>$email</strong></p>
							<p>Wiadomość: $message</p>
							<br><br> 
							<p>Adres IP: <strong>$address_ip</strong></p>
							<p>USER_AGENT: <strong>$user_data</strong></p>        
				 
						");

                $mail->AddAddress(MAIL_ADDRESS, MAIL_ADDRESS_NAME);

                if (!$mail->Send()) {
                    $messagetitle = "Błąd podczas wysyłania wiadomości!";
                    $messagetext = "Wiadomość nie została wysłana z powodu błędu: $mail->ErrorInfo.";
                } else {
                    $messagetitle = "Wiadomość wysłana!";
                    $messagetext = "Dziękujęmy za zainteresowanie. W najbliższym czasie uzyskasz odpowiedź na wskazany przez siebie adres.";
                    $name = $email = $message = null;
                }
            } else {
                // set the error code so that we can display it
                $error = $resp->error;
                $messagetitle = "Błąd reCAPTCHA!";
                $messagetext = $error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js">
    <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Zamek Kamieniec w Korczynie-Odrzykoniu - Kontakt</title>
        <meta name="description" content="Kontakt w sprawach związanych ze zwiedzaniem ruin zamku, muzeum, kawiarnią, organizacją imprez itp.">
        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" href="css/normalize.css">
        <link rel="stylesheet" href="css/main.css">
        <script src="js/vendor/modernizr-2.6.2.min.js"></script>
        <!-- Google WebFonts -->
        <link href='http://fonts.googleapis.com/css?family=Marcellus+SC|Inder|Crete+Round:400,400italic|Metamorphous|Berkshire+Swash&subset=latin-ext' rel='stylesheet'
              type='text/css'>
        <!-- Google Analytics -->
        <script type="text/javascript">
                var _gaq = _gaq || [];
                var pluginUrl =  '//www.google-analytics.com/plugins/ga/inpage_linkid.js';
                _gaq.push(['_require', 'inpage_linkid', pluginUrl]);
                _gaq.push(['_setAccount', 'UA-38785096-1']);
                _gaq.push(['_trackPageview']);
        
                (function() {
                        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
                })();
        </script>
    </head>
    <body>
        <!--[if lt IE 7]>
                <p class="chromeframe">Używasz <strong>przestarzałej</strong> przeglądarki. Aby poprawnie wyświetlić bieżącą stronę, musisz <a href="http://browsehappy.com/">zaktualizować swoją przeglądarkę</a> lub <a href="http://www.google.com/chromeframe/?redirect=true">zainstalować wtyczkę Google Chrome Frame</a>.</p>
        <![endif]-->

        <header>

            <h1 class="visuallyhidden">Zamek Kamieniec w Korczynie-Odrzykoniu</h1>
            <nav>
                <h2 class="visuallyhidden">Główna nawigacja</h2>
                <ul>
                    <li id="home"><a href="index.html" title="Powrót na stronę główną">Strona główna</a></li>
                    <li id="history"><a href="historia.html" title="Dzieje zamku odrzykońskiego">Historia zamku</a></li>
                    <li id="cafe"><a href="kawiarnia.html" title="Kawiarnia zamkowa">Kawiarnia zamkowa</a></li>
                    <li id="museum"><a href="muzeum.html" title="Muzeum na zamku">Muzeum</a></li>
                    <li id="offer"><a href="oferta.html" title="Nasza oferta wraz z cennikiem">Oferta i cennik</a></li>
                    <li id="gallery"><a href="galeria.html" title="Zdjęcia z zamku i okolic">Galeria zdjęć</a></li>
                </ul>
            </nav>
        </header>
        <section id="mainContent">
            <div class="leftCol">
                <h1>Formularz kontaktowy</h1>
                <form name="mailform" id="mailform" method="post" action="">
                    <p>
                        <label for="name">Twoje imię:</label>
                        <input type="text" name="name" id="name" tabindex="10" placeholder="Wpisz swoje imię" <?php echo ($name ? 'value="' . $name . '"' : '') ?> required>
                    </p>
                    <p>
                        <label for="email">Twój e-mail:</label>
                        <input type="email" name="email" id="email" tabindex="20" placeholder="np. nazwa@domena.pl" <?php echo ($email ? 'value="' . $email . '"' : '') ?> required>
                    </p>
                    <p>
                        <label for="message">Treść wiadomości:</label>
                        <textarea name="message" id="message" tabindex="30" required><?php echo ($message ? $message : '') ?></textarea>
                    </p>
                    <div id="captcha">
                        <div id="recaptcha">
                            <?php echo recaptcha_get_html($publickey, $error); ?>
                        </div>
                    </div>
                    <p>
                        <input type="submit" name="send" id="send" value="Wyślij" tabindex="40">
                        <input type="reset" name="reset" id="reset" value="Wyczyść" tabindex="50">
                    </p>
                </form>
            </div>
            <div class="rightCol">
                <article>
                    <p>Możesz wysłać do nas wiadomość e-mail, wypełniając formularz zamieszczony obok, lub skontaktować się telefonicznie dzwoniąc pod jeden z&nbsp;poniższych numerów:<br>
                        <span class="phones">788-837-412<br>
                            888-959-661</span><br>
                    </p>
                </article>
                <aside <?php echo ($messageerror ? 'class="messages error"' : ($messagetitle ? 'class="messages"' : 'class="messages hidden"')) ?>>
                    <h3><?php echo $messagetitle; ?></h3>
                    <p><?php echo $messagetext ?></p>
                </aside>
            </div>
        </section>
        <!-- end #mainContent -->
        <footer>
            <address>
                38-420 Korczyna, ul. Podzamcze b/n, tel: 888-959-661, 134-325-126, 788-837-412
            </address>
            <a href="http://js-projekt.pl/" target="_blank" id="jsprojekt"> <img src="img/js-projekt.png" alt="Logo JS-Projekt" title="Projekt i realizacja - JS-Projekt"></a>
        </footer>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script> 
        <script>
            window.jQuery || document.write('<script src="js/vendor/jquery-1.6.4.min.js"><\/script>');
        </script> 
        <script src="js/plugins.js"></script> 
    </body>
</html>
