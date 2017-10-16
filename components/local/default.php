<div id="local-box">
    <div class="container">
        <div class="panel panel-local display-local">
            <div class="panel-heading">
                <?= partial('@top'); ?>
            </div>

            <div class="panel-body">
                <div class="row boxes">
                    <div class="box-one col-xs-12 col-sm-5">
                        <?= partial('@box_one'); ?>
                    </div>
                    <div class="col-xs-12 box-divider visible-xs"></div>
                    <div class="box-two col-xs-12 col-sm-3">
                        <?= partial('@box_two'); ?>
                    </div>
                    <div class="col-xs-12 box-divider visible-xs"></div>
                    <div class="box-three col-xs-12 col-sm-4 col-md-4">
                        <?= partial('@box_three'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>