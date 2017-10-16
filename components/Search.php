<?php namespace SamPoyigi\Local\Components;

use App;
use ApplicationException;
use Exception;
use Igniter\Classes\GeoPosition;
use AjaxException;
use Sampoyigi\Local\Models\Settings_model;
use Request;

class Search extends \System\Classes\BaseComponent
{
    public $showSearch;

    public $hasSearchQuery;

    public $searchQueryRequired;

    public $countReviews;

    public function __construct($page = null, array $properties = [])
    {
        parent::__construct($page, $properties);
//        $this->location = App::make('');
    }

    public function onRun()
    {
        $this->addCss('stylesheet.css', 'local-module-css');
        $this->addJs('local.js', 'local-module-js');

//        $this->localModel = $this->location->getModel();
        $this->showSearch = (Settings_model::get('location_search_mode') == 1);
//        $useLocation = Settings_model::get('use_location');
//
//        if ($this->renderSearch AND !$this->showSearch AND $useLocation) {
//            $this->location->useLocation($useLocation)->initialize();
//        }
//
//        if (!$this->location->isInitialized())
//            $this->location->initialize();

        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $this->page['showSearch'] = $this->showSearch;
        $this->page['searchEventHandler'] = $this->getEventHandler('onSearchLocal');
        $this->page['showMenuButton'] = ($this->controller->getClass() != 'local');
        $this->page['localMenuUrl'] = restaurant_url('menus');
        $this->page['searchQuery'] = null; //$this->location->searchQuery();
    }

    public function onSearchLocal()
    {
        $json = [];

        try {
            $this->location->initialize();
            $result = $this->location->searchRestaurant(post('search_query'));

            if ($result instanceof GeoPosition) {
                switch ($result->status) {
                    case 'FAILED':
                        throw new ApplicationException(lang('sampoyigi.local::default.alert_unknown_error'));
                    case 'NO_SEARCH_QUERY':
                        throw new ApplicationException(lang('sampoyigi.local::default.alert_no_search_query'));
                    case 'INVALID_SEARCH_QUERY':
                        throw new ApplicationException(lang('sampoyigi.local::default.alert_invalid_search_query'));    // display error: enter postcode
                }
            }

            if (!$result OR $result->boundary == 'outside') {
                throw new ApplicationException(lang('sampoyigi.local::default.alert_no_found_restaurant'));    // display error: no available restaurant
            }

            $json['redirect'] = restaurant_url('menus');
        } catch (Exception $ex) {
            if (Request::ajax()) throw new AjaxException($ex->getMessage());
            else flash()->danger($ex->getMessage());
        }

        return $json;
    }

    protected function overrideDeliveryConditionText()
    {
        $this->location->area()->overrideConditionSummaryText([
            'all'       => lang('sampoyigi.local::default.text_condition_all_orders'),
            'above'     => lang('sampoyigi.local::default.text_condition_above_total'),
            'below'     => lang('sampoyigi.local::default.text_condition_below_total'),
            'free'      => lang('sampoyigi.local::default.text_free_delivery'),
            'min_total' => lang('sampoyigi.local::default.text_no_min_total'),
            'prefix'    => lang('sampoyigi.local::default.text_delivery_charge'),
        ]);
    }

    public function index()
    {
        $this->location->initialize();

        if ($this->setting('status') != '1') {
            return;
        }

        $this->addCss(extension_url('local/assets/stylesheet.css'), 'local-module-css');

        $data['location_search_mode'] = 'multi';
        if ($this->setting('location_search_mode') === 'single') {
            if ($this->setting('use_location')) {
                $use_location = $this->setting('use_location');
            }
            else if ($this->input->get('location_id')) {
                $use_location = $this->input->get('location_id');
            }
            else {
                $use_location = params('default_location_id');
            }

            $data['location_search_mode'] = 'single';
            if (!empty($use_location) AND is_numeric($use_location)) {
                $this->location->setLocation($use_location);
                $data['single_location_url'] = restaurant_url('menus?location_id='.$use_location);
            }
            else {
                $data['single_location_url'] = restaurant_url('local/all');
            }
        }

        $data['local_action'] = site_url('local/local/search');

        $data['rsegment'] = $rsegment = ($this->uri->rsegment(1) === 'local' AND !empty($this->referrer_uri)) ? $this->referrer_uri : $this->uri->rsegment(1);

        $this->load->library('cart');                                                            // load the cart library
        $cart_total = $this->cart->total();

        $data['info_url'] = site_url('local');
        $data['local_info'] = $this->location->local();
        $data['location_id'] = $this->location->getId();
        $data['location_name'] = $this->location->getName();
        $data['location_address'] = $this->location->getAddress();
        $data['location_image'] = $this->location->getImage();
        $data['is_opened'] = $this->location->isOpened();
        $data['opening_type'] = $this->location->workingType('opening');
        $data['opening_status'] = $this->location->workingStatus('opening');
        $data['delivery_status'] = $this->location->workingStatus('delivery');
        $data['collection_status'] = $this->location->workingStatus('collection');
        $data['opening_time'] = $this->location->workingTime('opening', 'open');
        $data['closing_time'] = $this->location->workingTime('opening', 'close');
        $data['order_type'] = $this->location->orderType();
        $data['delivery_charge'] = $this->location->deliveryCharge($cart_total);
        $data['delivery_coverage'] = $this->location->checkDeliveryCoverage();
        $data['search_query'] = $this->location->searchQuery();
        $data['has_search_query'] = $this->location->hasSearchQuery();
        $data['has_delivery'] = $this->location->hasDelivery();
        $data['has_collection'] = $this->location->hasCollection();
        $data['location_order'] = $this->config->item('location_order');

        $data['location_search'] = FALSE;
        if ($rsegment === 'home') {
            $data['location_search'] = TRUE;
        }

        if ($this->config->item('maps_api_key')) {
            $data['map_key'] = '&key='.$this->config->item('maps_api_key');
        }
        else {
            $data['map_key'] = '';
        }

        $data['delivery_time'] = $this->location->deliveryTime();
        if ($data['delivery_status'] === 'closed') {
            $data['delivery_time'] = 'closed';
        }
        else if ($data['delivery_status'] === 'opening') {
            $data['delivery_time'] = $this->location->workingTime('delivery', 'open');
        }

        $data['collection_time'] = $this->location->collectionTime();
        if ($data['collection_status'] === 'closed') {
            $data['collection_time'] = 'closed';
        }
        else if ($data['collection_status'] === 'opening') {
            $data['collection_time'] = $this->location->workingTime('collection', 'open');
        }

        $this->location->locationDelivery()->setChargeSummaryText([
            'all'       => lang('sampoyigi.local::default.text_condition_all_orders'),
            'above'     => lang('sampoyigi.local::default.text_condition_above_total'),
            'below'     => lang('sampoyigi.local::default.text_condition_below_total'),
            'free'      => lang('sampoyigi.local::default.text_free_delivery'),
            'min_total' => lang('sampoyigi.local::default.text_no_min_total'),
            'prefix'    => lang('sampoyigi.local::default.text_delivery_charge'),
        ]);

        $data['sampoyigi.local::default.text_delivery_condition'] = $this->location->getDeliveryChargeSummary();

        if ($this->location->deliveryCharge($cart_total) > 0) {
            $data['sampoyigi.local::default.text_delivery_charge'] = sprintf(lang('sampoyigi.local::default.text_delivery_charge'), $this->currency->format($this->location->deliveryCharge($cart_total)));
        }
        else {
            $data['sampoyigi.local::default.text_delivery_charge'] = lang('sampoyigi.local::default.text_free_delivery');
        }

        if ($this->location->minimumOrder($cart_total) > 0) {
            $data['min_total'] = $this->location->minimumOrder($cart_total);
        }
        else {
            $data['min_total'] = '0.00';
        }

        $this->load->model('Reviews_model');
        $total_reviews = $this->Reviews_model->getTotalLocationReviews($this->location->getId());
        $data['sampoyigi.local::default.text_total_review'] = sprintf(lang('sampoyigi.local::default.text_total_review'), $total_reviews);

        $data['local_alert'] = $this->alert->display('local');

        // pass array $data and load view files
        $this->load->view('local/local', $data);
    }

    public function search()
    {
        $this->load->library('user_agent');
        $json = [];

        $result = $this->location->searchRestaurant($this->input->post('search_query'));

        switch ($result) {
            case 'FAILED':
                $json['error'] = lang('alert_unknown_error');
                break;
            case 'NO_SEARCH_QUERY':
                $json['error'] = lang('alert_no_search_query');
                break;
            case 'INVALID_SEARCH_QUERY':
                $json['error'] = lang('alert_invalid_search_query');    // display error: enter postcode
                break;
            case 'outside':
                $json['error'] = lang('alert_no_found_restaurant');    // display error: no available restaurant
                break;
        }

        $redirect = '';
        if (!isset($json['error'])) {
            $redirect = $json['redirect'] = restaurant_url();
        }

        if ($redirect === '') {
            $redirect = $this->referrer_uri;
        }

        if ($this->input->is_ajax_request()) {
            return $json;
        }
        else {
            if (isset($json['error'])) $this->alert->set('custom', $json['error'], 'local');

            return $this->redirect($redirect);
        }
    }
}
