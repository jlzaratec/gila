

<footer class="wrapper" style="background:#464a49;margin-top:10px;color:white">
    <p class="copyright text-muted"><?=gila::option('theme.footer-text','Copyright &copy; Your Website 2017');?></p>
<?php
global $starttime;
$end = microtime(true);
$creationtime = ($end - $starttime);
//printf("<p>Page created in %.6f seconds.</p>", $creationtime);
?>
</footer>

</div>
</body>

</html>
