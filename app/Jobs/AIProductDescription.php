<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AIProductDescription implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private Product $product;

        /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->askToChatGPT();
    }

    function extractTextSections($input) {
        // Define the patterns for each section, accommodating ###
        $patterns = [
            'keywords' => '/### SEO Keywords\s*(.*?)(?=\n\s*### Meta Title|\n\s*### Meta Description|\n\s*### Product Description|$)/s',
            'title' => '/### Meta Title\s*(.*?)(?=\n\s*### SEO Keywords|\n\s*### Meta Description|\n\s*### Product Description|$)/s',
            'meta_description' => '/### Meta Description\s*(.*?)(?=\n\s*### SEO Keywords|\n\s*### Meta Title|\n\s*### Product Description|$)/s',
            'product_description' => '/### Product Description\s*(.*?)(?=\n\s*### SEO Keywords|\n\s*### Meta Title|\n\s*### Meta Description|$)/s',
        ];
    
        // Array to store the extracted sections
        $sections = [];
    
        // Loop through each pattern and extract the corresponding text
        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $input, $matches)) {
                $sections[$key] = trim($matches[1]);
            }
        }
    
        return $sections;
    }

    private function generateText($contents,$categoryName) {
        \Log::debug($contents);
        if ($contents) {
            $pos = 0;

            for ($i=0;$i < 3;$i++) {
                $pos = stripos($contents,":",$pos+2); // 13
                $newpos = stripos($contents,"\n",$pos+2); // 96 - 13
                $content = substr($contents, $pos+2,$newpos-$pos-2);
                switch ($i) {
                    case 0:
                        $data['keywords'] = $content;
                        break;
                    case 1:
                        $data['metatitle'] = $content;
                        break;
                    case 2:
                        $data['metadescription'] = $content;
                        break;
                }
            }//nishtageya
            
            
            //\Log::debug($contents);
            if ($categoryName == 'Rolex') {
                $pos = stripos($contents,"Product Description:"); // 13
                $content = substr($contents, $pos+20);
                $data['longdescription'] = $content;
            } else {
                $newpos = stripos($contents,"\n",$pos+2); // 96 - 13
                $content = substr($contents, $pos+2);
                $data['longdescription'] = $content;
            }
        }

        return $data;
    }

    private function askToChatGPT() 
    {

        if (!$this->product->p_longdescription) {

            $product = $this->product;
            $txt = "Brand: " . $product->categories->category_name . ", ";

            if ($product->p_model)
                $txt .= "Model:" . $product->p_model . ", ";
            
            if ($product->p_casesize)
                $txt .="Case Size:" . $product->p_casesize . ", ";

            if ($product->p_reference)
                $txt .="Reference: ". $product->p_reference  . ", ";

            if ($product->p_color)
                $txt .="Face Color: ". $product->p_color  . ", ";

            if ($product->p_color)
                $txt .="Face Color: ". $product->p_color  . ", ";

            if ($product->p_year)
                $txt .= "Production Year: " . $product->p_year . ", ";

            if ($product->p_box==1)
                 $box = "Yes,";
            else $box = "No, ";

            $custom_columns = getCustomColumns();
            if(!empty($custom_columns)) {
                foreach ($custom_columns as $column) {
                    if ($product->$column) {
                        $columnName = ucwords(str_replace(['-','c_'], ' ', $column));
                        $txt .= $columnName.": ".$product->$column;
                    }
                }
            }
            
            if ($product->p_papers==1)
                $papers = "Yes,";
            else $papers = "No, ";

            $txt .= "Box: " . $box;
            $txt .= "Papers: " . $papers;

            if ($product->p_strap>0)
                $txt .= "Strap/Band: ". Strap()->get($product->p_strap).", ";
            
            if ($product->p_dial_style)
                $txt .= "Indices: ".DialStyle()->get($product->p_dial_style);

            if ($product->p_clasp>0)
                $txt .= "Clasp Type: " . Clasps()->get($product->p_clasp) . ", ";

            if ($product->p_material>0)
                $txt .= "Case Material: ". Materials()->get($product->p_material) . ", ";
            
            if ($product->bezel_features)
                $txt .= "Bezel feature: ". $product->bezel_features.", ";
            
            if ($product->p_retail)
                $txt .= "Retail Value: ". $product->p_retail.", ";

            $txt .= "Gender: ".$product->p_gender;

            if ($product->p_bezelmaterial>0)
                $txt .= "Bezel Material: " . BezelMaterials()->get($product->p_bezelmaterial) . ", ";
            
            if ($product->water_resistance) 
                $txt .= "Water Resistance: " . $product->water_resistance . ", ";

            if ($product->movement>-1) 
                $txt .= "Movement: " . Movement()->get($product->movement) . ", ";

            // if ($product->p_smalldescription) 
            //     $txt .= $product->p_smalldescription . ", ";

            $apiUrl = 'https://api.openai.com/v1/chat/completions';
            $apiKey = config('chatgpt.CHATGPT_API_KEY');

            // Prepare the data to send in the POST request
            $postData = [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "generate SEO keywords comma seperated from the following title: " . 
                            "\n".$product->title."\nthen, create meta title and meta description.".
                            "Finally compose a product description based on the provided SEO keywords by additionally using this Information: $txt"
                    ]
                ],
                'temperature' => 0.7,
                    "max_tokens" => 3000,
                    "top_p" => 1.0,
                    "frequency_penalty" => 0.52,
                    "presence_penalty" => 0.5,
                    //"stop" => ["11."],
            ];

            // Make the HTTP request with SSL verification disabled
            $response = Http::withoutVerifying()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json'
            ])
            ->post($apiUrl, $postData);

            // Check if the response is successful
            if (!$response->ok()) {
                \Log::debug('Error occurred while making the HTTP request: ' . $response->status());
            }

            // Decode the JSON response
            $responseArray = $response->json();

            // Check for decoding errors
            if (!isset($responseArray['choices'][0]['message']['content'])) {
                \Log::debug('Unexpected response structure.');
            }

            if (isset($responseArray['error'])) {
                $data = $responseArray['error']['message'];
                $product->p_comments = $data;
                $product->update();
            } else {

                $data = $this->extractTextSections($responseArray['choices'][0]['message']['content']);
                if (isset($data['product_description']))
                    $product->p_longdescription = $data['product_description'];
                if (isset($data['title']))
                    $product->p_metatitle=$data['title'];
                if (isset($data['keywords']))
                    $product->p_keywords = $data['keywords'];

                if (isset($data['meta_description']))
                    $product->p_metadescription = $data['meta_description'];

                $product->update();
            }
                
        }
    }
}
