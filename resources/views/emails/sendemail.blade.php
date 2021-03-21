<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head></head>
    <body>
        <h2>From: {!! $text['senderemail'] !!}</h2><br>
        <h2>To: {!! $text['recepientemail'] !!}</h2><br>
        <h3>Subject: {!! $text['subject'] !!}</h2><br>
        <span>Text: <br>{!! $text['textContent'] !!}</span><br>
        <span>HTML: <br>{!! $text['htmlContent'] !!}</span>
    </body>
</html>