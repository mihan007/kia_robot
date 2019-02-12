<?
/**
 * @var $topic string
 */
?>
<div class="row">
    <div class="col-md-3 col-sm-4">
        <?php echo $this->render('_menu', ['topic' => $topic]); ?>
    </div>
    <div class="col-md-9 col-sm-8">
        <?php echo $this->render($topic); ?>
    </div>
</div>