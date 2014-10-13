<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <p class="flash-message panel panel-default bg-danger"><?= h($message) ?></p>
        <?php 
        if(isset($params['exception_message']))
        {
            echo '<p class="flash-message panel panel-default bg-danger"><?= h($message) ?>';
            echo h($params['exception_message']);
            echo '</p>';
        }
        ?>
    </div>
</div>