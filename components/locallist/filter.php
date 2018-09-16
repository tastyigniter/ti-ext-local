<div class="panel">
    <div class="panel-body">
        <form
            method="GET"
            id="filter-search-form"
            class="form-search form-horizontal"
            action="<?= current_url(); ?>"
        >
            <div class="input-group">
                <input
                    type="text"
                    class="form-control"
                    name="search"
                    value="<?= $filterSearch; ?>"
                    placeholder="<?= lang('igniter.local::default.text_filter_search'); ?>"
                />
                <span class="input-group-btn">
                    <button class="btn btn-outline-default" type="submit"><i class="fa fa-search"></i></button>
                    <a class="btn btn-outline-danger" href="<?= current_url(); ?>"><i class="fa fa-times"></i></a>
                </span>
            </div>
        </form>
    </div>
    <ul class="list-group list-group-responsive">
        <?php foreach ($filterSorters as $key => $filter) { ?>
            <li class="list-group-item  <?= ($key == $filterSorted) ? 'disabled' : '' ?>">
                <a
                    class="btn-block"
                    <?= ($key == $filterSorted) ? 'disabled' : 'href="'.$filter['href'].'"'; ?>
                >
                    <i class="fa fa-angle-right"></i>&nbsp;&nbsp;<?= $filter['name']; ?>
                </a>
            </li>
        <?php } ?>
    </ul>
</div>
