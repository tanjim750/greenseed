<?php
namespace App\Utils;

use App\Models\ProductStock;

class Util {

	public function expenseStatus($expense){

		if ($expense) {
			$due=$expense->amount - $expense->payments()->sum('amount');
			if ($due==0) {
				$status='paid';
			}else if($due ==$expense->amount){
				$status='due';
			}else{
				$status='partial';
			}

			$expense->payment_status=$status;
			$expense->save();
		}
		return true;

	}

	public function purchaseStatus($purchase){

		if ($purchase) {
			$due=$purchase->amount - $purchase->payments()->sum('amount');
			if ($due==0) {
				$status='paid';
			}else if($due ==$purchase->amount){
				$status='due';
			}else{
				$status='partial';
			}

			$purchase->payment_status=$status;
			$purchase->save();
		}
		return true;

	}


	public function increaseProductStock($product_id,$variation_id, $stock){

		$item=ProductStock::where(['product_id'=>$product_id,'variation_id'=>$variation_id])->first();

		if ($item) {
			
		}
		else{
			$item=new ProductStock();
			$item->product_id=$product_id;
			$item->variation_id=$variation_id;
			$item->quantity=0;
		}

		$item->quantity+=$stock;
		$item->save();


		return true;

	}


	public function updateProductStock($product_id, $variation_id, $old_stock, $new_stock){

		$item=ProductStock::where(['product_id'=>$product_id, 'variation_id'=>$variation_id])->first();
		$stock=$new_stock -$old_stock;
		if ($stock !=0) {
			if ($item) {
				
			}else{
				$item=new ProductStock();
				$item->product_id=$product_id;
				$item->variation_id=$variation_id;
				$item->quantity=0;
			}

			$item->quantity +=$stock;
			$item->save();

		}
		return true;

		

	}


	public function decreaseProductStock($product_id, $variation_id, $stock){

		$item=ProductStock::where(['product_id'=>$product_id, 'variation_id'=>$variation_id])->first();

		if($item){
			$item->quantity-=$stock;
			$item->save();
		}
		
		return true;


	}


	public function checkProductStock($product_id, $variation_id){

		$item=ProductStock::where(['product_id'=>$product_id, 'variation_id'=>$variation_id])->first();
		
		return $item?$item->quantity:0;


	}
	
	public static function uploadFile($file, $folder)
	{
	    if(!empty($file && $folder))
        {
            $new_name = rand().'.'.$file->extension();
            $file->move(public_path('uploads/'.$folder), $new_name);
            return $new_name;
        }
	}
		
		
	public static function deleteFile($file, $folder)
	{
       if(!empty($file && $folder))
       {
            $path = public_path('uploads/'.$folder.'/').$file;
            if(file_exists($path))
            {
                unlink($path);
            }
       }
        
        return true;
	}
	




}