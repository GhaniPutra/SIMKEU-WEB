<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sales')->insert([
            ['id'=>15,'user_id'=>5,'invoice_number'=>'INV-20260518-140243-2ALN','transaction_date'=>'2026-05-18 14:02:43','subtotal'=>'11000.00','tax'=>'0.00','discount'=>'0.00','total'=>'11000.00','payment_method'=>'tunai','notes'=>null,'created_at'=>'2026-05-18 07:02:43','updated_at'=>'2026-05-18 07:02:43'],
            ['id'=>16,'user_id'=>5,'invoice_number'=>'INV-20260518-140258-SXSE','transaction_date'=>'2026-05-18 14:02:58','subtotal'=>'12000.00','tax'=>'0.00','discount'=>'0.00','total'=>'12000.00','payment_method'=>'tunai','notes'=>null,'created_at'=>'2026-05-18 07:02:58','updated_at'=>'2026-05-18 07:02:58'],
            ['id'=>17,'user_id'=>5,'invoice_number'=>'INV-20260518-140315-E3PT','transaction_date'=>'2026-05-18 14:03:15','subtotal'=>'16000.00','tax'=>'0.00','discount'=>'0.00','total'=>'16000.00','payment_method'=>'transfer','notes'=>null,'created_at'=>'2026-05-18 07:03:15','updated_at'=>'2026-05-18 07:03:15'],
            ['id'=>18,'user_id'=>5,'invoice_number'=>'INV-20260518-140332-3NT8','transaction_date'=>'2026-05-18 14:03:32','subtotal'=>'12000.00','tax'=>'0.00','discount'=>'0.00','total'=>'12000.00','payment_method'=>'transfer','notes'=>null,'created_at'=>'2026-05-18 07:03:32','updated_at'=>'2026-05-18 07:03:32'],
            ['id'=>19,'user_id'=>5,'invoice_number'=>'INV-20260519-062000-LBVK','transaction_date'=>'2026-05-19 06:20:00','subtotal'=>'37000.00','tax'=>'0.00','discount'=>'0.00','total'=>'37000.00','payment_method'=>'tunai','notes'=>null,'created_at'=>'2026-05-18 23:20:00','updated_at'=>'2026-05-18 23:20:00'],
            ['id'=>20,'user_id'=>5,'invoice_number'=>'INV-20260522-070944-MICJ','transaction_date'=>'2026-05-22 07:09:44','subtotal'=>'12000.00','tax'=>'0.00','discount'=>'0.00','total'=>'12000.00','payment_method'=>'tunai','notes'=>null,'created_at'=>'2026-05-22 00:09:44','updated_at'=>'2026-05-22 00:09:44'],
            ['id'=>21,'user_id'=>5,'invoice_number'=>'INV-20260522-071744-X5S7','transaction_date'=>'2026-05-22 07:17:44','subtotal'=>'14000.00','tax'=>'0.00','discount'=>'0.00','total'=>'14000.00','payment_method'=>'tunai','notes'=>null,'created_at'=>'2026-05-22 00:17:44','updated_at'=>'2026-05-22 00:17:44'],
            ['id'=>22,'user_id'=>5,'invoice_number'=>'INV-20260522-072337-LWH1','transaction_date'=>'2026-05-22 07:23:37','subtotal'=>'16000.00','tax'=>'0.00','discount'=>'0.00','total'=>'16000.00','payment_method'=>'tunai','notes'=>null,'created_at'=>'2026-05-22 00:23:37','updated_at'=>'2026-05-22 00:23:37'],
            ['id'=>23,'user_id'=>5,'invoice_number'=>'INV-20260522-165856-ZVWV','transaction_date'=>'2026-05-22 16:58:56','subtotal'=>'12000.00','tax'=>'0.00','discount'=>'0.00','total'=>'12000.00','payment_method'=>'tunai','notes'=>null,'created_at'=>'2026-05-22 09:58:56','updated_at'=>'2026-05-22 09:58:56'],
            ['id'=>24,'user_id'=>5,'invoice_number'=>'INV-20260522-170142-J5GZ','transaction_date'=>'2026-05-22 17:01:42','subtotal'=>'53000.00','tax'=>'0.00','discount'=>'0.00','total'=>'53000.00','payment_method'=>'tunai','notes'=>null,'created_at'=>'2026-05-22 10:01:42','updated_at'=>'2026-05-22 10:01:42'],
            ['id'=>25,'user_id'=>5,'invoice_number'=>'INV-20260522-170740-TBGL','transaction_date'=>'2026-05-22 17:07:40','subtotal'=>'65000.00','tax'=>'0.00','discount'=>'0.00','total'=>'65000.00','payment_method'=>'tunai','notes'=>null,'created_at'=>'2026-05-22 10:07:40','updated_at'=>'2026-05-22 10:07:40'],
            ['id'=>26,'user_id'=>5,'invoice_number'=>'INV-20260522-171611-T3NI','transaction_date'=>'2026-05-22 17:16:11','subtotal'=>'65000.00','tax'=>'0.00','discount'=>'0.00','total'=>'65000.00','payment_method'=>'tunai','notes'=>null,'created_at'=>'2026-05-22 10:16:11','updated_at'=>'2026-05-22 10:16:11'],
            ['id'=>27,'user_id'=>5,'invoice_number'=>'INV-20260527-192828-KXHA','transaction_date'=>'2026-05-27 19:28:28','subtotal'=>'16000.00','tax'=>'0.00','discount'=>'0.00','total'=>'16000.00','payment_method'=>'transfer','notes'=>null,'created_at'=>'2026-05-27 12:28:28','updated_at'=>'2026-05-27 12:28:28'],
        ]);

        DB::table('sale_items')->insert([
            ['id'=>6,'sale_id'=>15,'product_id'=>3,'quantity'=>1,'price_at_sale'=>'11000.00','subtotal'=>'11000.00','created_at'=>'2026-05-18 14:02:43'],
            ['id'=>7,'sale_id'=>16,'product_id'=>2,'quantity'=>1,'price_at_sale'=>'12000.00','subtotal'=>'12000.00','created_at'=>'2026-05-18 14:02:58'],
            ['id'=>8,'sale_id'=>17,'product_id'=>5,'quantity'=>1,'price_at_sale'=>'16000.00','subtotal'=>'16000.00','created_at'=>'2026-05-18 14:03:15'],
            ['id'=>9,'sale_id'=>18,'product_id'=>6,'quantity'=>1,'price_at_sale'=>'12000.00','subtotal'=>'12000.00','created_at'=>'2026-05-18 14:03:32'],
            ['id'=>10,'sale_id'=>19,'product_id'=>3,'quantity'=>1,'price_at_sale'=>'11000.00','subtotal'=>'11000.00','created_at'=>'2026-05-19 06:20:00'],
            ['id'=>11,'sale_id'=>19,'product_id'=>4,'quantity'=>1,'price_at_sale'=>'14000.00','subtotal'=>'14000.00','created_at'=>'2026-05-19 06:20:00'],
            ['id'=>12,'sale_id'=>19,'product_id'=>6,'quantity'=>1,'price_at_sale'=>'12000.00','subtotal'=>'12000.00','created_at'=>'2026-05-19 06:20:00'],
            ['id'=>13,'sale_id'=>20,'product_id'=>2,'quantity'=>1,'price_at_sale'=>'12000.00','subtotal'=>'12000.00','created_at'=>'2026-05-22 07:09:44'],
            ['id'=>14,'sale_id'=>21,'product_id'=>4,'quantity'=>1,'price_at_sale'=>'14000.00','subtotal'=>'14000.00','created_at'=>'2026-05-22 07:17:44'],
            ['id'=>15,'sale_id'=>22,'product_id'=>5,'quantity'=>1,'price_at_sale'=>'16000.00','subtotal'=>'16000.00','created_at'=>'2026-05-22 07:23:37'],
            ['id'=>16,'sale_id'=>23,'product_id'=>2,'quantity'=>1,'price_at_sale'=>'12000.00','subtotal'=>'12000.00','created_at'=>'2026-05-22 16:58:56'],
            ['id'=>17,'sale_id'=>24,'product_id'=>3,'quantity'=>1,'price_at_sale'=>'11000.00','subtotal'=>'11000.00','created_at'=>'2026-05-22 17:01:42'],
            ['id'=>18,'sale_id'=>24,'product_id'=>4,'quantity'=>1,'price_at_sale'=>'14000.00','subtotal'=>'14000.00','created_at'=>'2026-05-22 17:01:42'],
            ['id'=>19,'sale_id'=>24,'product_id'=>6,'quantity'=>1,'price_at_sale'=>'12000.00','subtotal'=>'12000.00','created_at'=>'2026-05-22 17:01:42'],
            ['id'=>20,'sale_id'=>24,'product_id'=>5,'quantity'=>1,'price_at_sale'=>'16000.00','subtotal'=>'16000.00','created_at'=>'2026-05-22 17:01:42'],
            ['id'=>21,'sale_id'=>25,'product_id'=>5,'quantity'=>1,'price_at_sale'=>'16000.00','subtotal'=>'16000.00','created_at'=>'2026-05-22 17:07:40'],
            ['id'=>22,'sale_id'=>25,'product_id'=>2,'quantity'=>1,'price_at_sale'=>'12000.00','subtotal'=>'12000.00','created_at'=>'2026-05-22 17:07:40'],
            ['id'=>23,'sale_id'=>25,'product_id'=>6,'quantity'=>1,'price_at_sale'=>'12000.00','subtotal'=>'12000.00','created_at'=>'2026-05-22 17:07:40'],
            ['id'=>24,'sale_id'=>25,'product_id'=>4,'quantity'=>1,'price_at_sale'=>'14000.00','subtotal'=>'14000.00','created_at'=>'2026-05-22 17:07:40'],
            ['id'=>25,'sale_id'=>25,'product_id'=>3,'quantity'=>1,'price_at_sale'=>'11000.00','subtotal'=>'11000.00','created_at'=>'2026-05-22 17:07:40'],
            ['id'=>26,'sale_id'=>26,'product_id'=>3,'quantity'=>1,'price_at_sale'=>'11000.00','subtotal'=>'11000.00','created_at'=>'2026-05-22 17:16:11'],
            ['id'=>27,'sale_id'=>26,'product_id'=>4,'quantity'=>1,'price_at_sale'=>'14000.00','subtotal'=>'14000.00','created_at'=>'2026-05-22 17:16:11'],
            ['id'=>28,'sale_id'=>26,'product_id'=>2,'quantity'=>1,'price_at_sale'=>'12000.00','subtotal'=>'12000.00','created_at'=>'2026-05-22 17:16:11'],
            ['id'=>29,'sale_id'=>26,'product_id'=>6,'quantity'=>1,'price_at_sale'=>'12000.00','subtotal'=>'12000.00','created_at'=>'2026-05-22 17:16:11'],
            ['id'=>30,'sale_id'=>26,'product_id'=>5,'quantity'=>1,'price_at_sale'=>'16000.00','subtotal'=>'16000.00','created_at'=>'2026-05-22 17:16:11'],
            ['id'=>31,'sale_id'=>27,'product_id'=>5,'quantity'=>1,'price_at_sale'=>'16000.00','subtotal'=>'16000.00','created_at'=>'2026-05-27 19:28:28'],
        ]);
    }
}
