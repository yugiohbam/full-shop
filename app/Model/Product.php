<?php namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Model\File;
use Response;

class Product extends BaseModel
{
    protected $table = "product";
    protected $fillable = ['id', 'name', 'description', 'price', 'ratio', 'option', 'sku', 'attributes', 'view', 'buy_times', 'brand', 'sale_price', 'category_id', 'status', 'quantity', 'image_id'];

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setModelClass('App\Model\Product');
        $this->setSingularKey('product');
        $this->setPluralKey('products');
    }

    public function getCategoryProducts($category_id, $filter = array())
    {
        $model_class = $this->getModelClass();
        try {
            $products = $model_class::where('category_id', $category_id)->orderBy($filter['order-by'], $filter['direction'])->paginate($filter['limit']);
            if (empty($products))
                return Response::json(['errors' => ['message' => trans('message.product_not_exist')]]);
            foreach ($products as $product) {
                if (!empty($product->image_id)) {
                    $file = new File();
                    $response = $file->show($product->image_id);
                    $data = json_decode($response->getContent(), 'true');
                    if (isset($data['errors']))
                        return Response::json(['errors' => ['message' => $data['errors']['message']]]);
                    $file = $data['file'];
                    $product->image_id = $file['id'];
                    $product->image_url = $file['url'];
                }
            }
            return $products;
        } catch (Exception $e) {
            return Response::json(['errors' => [
                'code' => $e->getCode(), 'message' => $e->getMessage()
            ]
            ]);
        }
    }

    public function search($key)
    {
        $model_class = $this->getModelClass();
        try {
            $model = $model_class::where('name', 'like', '%' . $key . '%')->get(['id', 'name', 'sku', 'image_id']);
            if (!$model)
                return Response::json(['errors' => ['message' => trans('message.product_not_exist')]]);
            foreach ($model as $key => $product) {
                if (!empty($product->image_id)) {
                    $file = new File();
                    $response = $file->show($product->image_id);
                    $data = json_decode($response->getContent(), 'true');
                    if (isset($data['errors']))
                        return Response::json(['errors' => ['message' => $data['errors']['message']]]);
                    $file = $data['file'];
                    $product->image_url = $file['url'];
                }
            }
            return Response::json([$this->getPluralKey() => $model]);
        } catch (Exception $e) {
            return Response::json(['errors' => ['message' => $e->getMessage()]]);
        }
    }

    public function show($id = null)
    {
        $model_class = $this->getModelClass();
        try {
            $model = $model_class::find($id);
            if (!$model)
                return Response::json(['errors' => ['message' => trans('message.product_not_exist')]]);
            if (!empty($model->image_id)) {
                $file = new File();
                $response = $file->show($model->image_id);
                $data = json_decode($response->getContent(), 'true');
                if (isset($data['errors']))
                    return Response::json(['errors' => ['message' => trans('message.product_not_exist')]]);
                $file = $data['file'];
                $model->image_id = $file['id'];
                $model->image_url = "/images/product/" . $file['name'];
            }
            empty($model['option']) or $model['option'] = unserialize($model['option']);
            return Response::json([$this->getSingularKey() => $model]);
        } catch (Exception $e) {
            return Response::json(['errors' => ['message' => $e->getMessage()]]);
        }
    }


    public function getSku($sku = null)
    {
        $model_class = $this->getModelClass();
        try {
            $product = $model_class::where('sku', $sku)->first();
            if (!$product)
                return Response::json(['errors' => ['message' => trans('message.product_not_exist')]]);
            if (isset($product->image_id)) {
                $file = new File();
                $response = $file->show($product->image_id);
                $data = json_decode($response->getContent(), 'true');
                if (isset($data['errors']))
                    return Response::json(['errors' => ['message' => $data['errors']['message']]]);
                $file = $data['file'];
                $product->image_id = $file['id'];
                $product->image_url = "/images/product/" . $file['name'];
            }
            empty($product['option']) or $product['option'] = unserialize($product['option']);
            return Response::json([$this->getSingularKey() => $product]);
        } catch (Exception $e) {
            return Response::json(['errors' => ['message' => $e->getMessage()]]);
        }
    }

    public function getLimit($filter = array(),$attributes = [],$order=['key'=>"id",'aspect' => 'DESC'],$limit=4)
    {
        $model_class = $this->getModelClass();
        try {
            $model = $model_class::where($filter)->orderBy($order['key'],$order['aspect'])->take($limit)->get($attributes);
            if (!$model)
                return Response::json(['errors' => ['message' => trans('message.'.$this->getSingularKey(). '_not_exist')]]);

            foreach ($model as $key => $product) {
                if (!empty($product->image_id)) {
                    $file = new File();
                    $response = $file->show($product->image_id);
                    $data = json_decode($response->getContent(), 'true');
                    if (isset($data['errors']))
                        return Response::json(['errors' => ['message' => $data['errors']['message']]]);
                    $file = $data['file'];
                    $product->image_url = $file['url'];
                }
            }
            return Response::json([$this->getPluralKey() => $model]);
        } catch (Exception $e) {
            return Response::json(['errors' => ['code' => $e->getCode(), 'message' => $e->getMessage()]]);
        }
    }

}
