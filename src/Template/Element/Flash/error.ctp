<?php 
echo $this->Html->script('Alaxos.alaxos/flashMessage');
?>

<div class="row flash-message-row">
    <div class="col-md-6 col-md-offset-3">
        
        <p class="flash-message panel panel-default bg-danger">
        <?php 
        echo $this->Html->image('Alaxos.close.png', ['style' => 'float:right;margin-top:-12px;margin-right:-12px;']);
        ?>
        
        <?= h($message) ?>
        </p>
        
        <?php 
        if(isset($params['exception_message']))
        {
            echo '<p class="flash-message panel panel-default bg-danger">';
            echo h($params['exception_message']);
            echo '</p>';
        }
        ?>
        
    </div>
</div>
