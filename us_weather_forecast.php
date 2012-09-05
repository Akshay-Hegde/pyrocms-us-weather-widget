<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Widget_Us_weather_forecast extends Widgets
{
    // The widget title,  this is displayed in the admin interface
    public $title = 'US Weather Forecast';

    //The widget description, this is also displayed in the admin interface.  Keep it brief.
    public $description =  'Display a 5 day weather forecast. Powered by Yahoo! Weather.';

    // The author's name
    public $author = 'Zac Vineyard';

    // The authors website for the widget
    public $website = 'http://zacvineyard.com/';

    //current version of your widget
    public $version = '1.0';

    /**
     * $fields array fore storing widget options in the database.
     * values submited through the widget instance form are serialized and
     * stored in the database.
     */
    public $fields = array(
        array(
            'field'   => 'zip',
            'label'   => 'US Zip Code',
            'rules'   => 'required'
        )
    );

    /**
     * Get and parse the weather data from Yahoo!
     */
    public function get_forecast($url,$options)
    {

        $obj = false;

        $opts = array('http' =>
            array(
                'method'  => 'GET',
                'timeout' => 1 
            )
        );
        $context  = stream_context_create($opts);

        // Cache
        if (!$xml = $this->pyrocache->get('yahoo_weather/' . md5('yahoo_weather-' . url_title($options['zip']))))
        {
            $zip = urlencode($options['zip']);
            $xml = utf8_encode(str_replace("yweather:forecast","forecast",file_get_contents($url, false, $context)));

            // Write cache with 6 hour expiration
            $this->pyrocache->write($xml, 'yahoo_weather/' . md5('yahoo_weather-' . url_title($options['zip'])), 21600);
        }

        if($xml)
        {
            $obj = simplexml_load_string($xml);  
        }
        if($obj !== false)
        {
            $output = array();
            foreach($obj->channel->item as $node)
            {
                $counter = 0;
                foreach($node->forecast as $v)
                {
                    $output[$counter]['day'] = (string) $v->attributes()->day;
                    $output[$counter]['high'] = (string) $v->attributes()->high;
                    $output[$counter]['low'] = (string) $v->attributes()->low;
                    $output[$counter]['condition'] = (string) $v->attributes()->text;
                    $output[$counter]['condition_code'] = (string) $v->attributes()->code;
                    $counter++;
                }
            }
            return $output;
        }
        else
        {
            return false;
        }
    }

    /**
     * the $options param is passed by the core Widget class.  If you have
     * stored options in the database,  you must pass the paramater to access
     * them.
     */
    public function run($options)
    {
        $forecast = array();
        if(!empty($options['zip']))
        {
            // Get the weather data
            $url = 'http://xml.weather.yahoo.com/forecastrss/'.$options['zip'].'_f.xml';
            $forecast = $this->get_forecast($url,$options);
        }
        else
        {
            return array('output' => '');
        }

        // Store the feed items
        return array(
            'zip' => $options['zip'],
            'forecast' => $forecast ? $forecast : array()
        );
    }

}