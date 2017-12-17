
<?php 

include("menu.php");

include ("ThreeDPaymentInitRequest.php");
include ("ThreeDPaymentCompleteRequest.php");

ini_set('display_errors',1); 
error_reporting(E_ALL );


/*
 * 	3D Secure işleminin 2. adımında 1. adımda bizlere post edilen dataları alıyoruz.
 *	Bu dataları settings ayarlarımızla birlikte Validate3DReturn metoduna post ediyoruz. 
 *  ThreeDPaymentCompleteRequest sınıfımıza ürün,sipariş ve müşteri bilgilerimizle post ediyoruz.
 * Eğer işlem başarılı ise başarılı mesajını ekranda gösteriyoruz.
 * Başarılı değilse fail.php sayfasına gönderiyoruz.
*/

$settings = new Settings();


$paymentResponse=new ThreeDPaymentInitResponse();


$paymentResponse->OrderId =$_POST['orderId'];
$paymentResponse->Result=$_POST['result'];
$paymentResponse->Amount=$_POST['amount'];
$paymentResponse->Mode=$settings->Mode;
$paymentResponse->ErrorCode=$_POST['errorCode'];
$paymentResponse->ErrorMessage=$_POST['errorMessage'];
$paymentResponse->TransactionDate=$_POST['transactionDate'];
$paymentResponse->Hash= $_POST['hash'];

if (Helper::Validate3DReturn($paymentResponse, $settings))
{
$request = new ThreeDPaymentCompleteRequest();
$request->OrderId = $_POST['orderId'];
$request->Echo = "Echo";
$request->Mode = $settings->Mode;
$request->Amount = "10000"; // 100 tL
$request->CardOwnerName = "Fatih Coşkun";
$request->CardNumber = "4282209027132016";
$request->CardExpireMonth = "05";
$request->CardExpireYear = "18";
$request->Installment = "1";
$request->Cvc = "000";
$request->ThreeD = "true";
$request->ThreeDSecureCode = $_POST['threeDSecureCode'];


#region Sipariş veren bilgileri
$request->Purchaser = new Purchaser();
$request->Purchaser->BirthDate = "1986-07-11";
$request->Purchaser->GsmPhone = "5881231212";
$request->Purchaser->IdentityNumber = "1234567890";
#endregion

#region Fatura bilgileri

$request->Purchaser->InvoiceAddress = new PurchaserAddress();
$request->Purchaser->InvoiceAddress->Name = "Murat";
$request->Purchaser->InvoiceAddress->SurName = "Kaya";
$request->Purchaser->InvoiceAddress->Address = "Mevlüt Pehlivan Mah-> Multinet Plaza Şişli";
$request->Purchaser->InvoiceAddress->ZipCode = "34782";
$request->Purchaser->InvoiceAddress->CityCode = "34";
$request->Purchaser->InvoiceAddress->IdentityNumber = "1234567890";
$request->Purchaser->InvoiceAddress->CountryCode = "TR";
$request->Purchaser->InvoiceAddress->TaxNumber = "123456";
$request->Purchaser->InvoiceAddress->TaxOffice = "Kozyatağı";
$request->Purchaser->InvoiceAddress->CompanyName = "iPara";
$request->Purchaser->InvoiceAddress->PhoneNumber = "2122222222";
#endregion

#region Kargo Adresi bilgileri
$request->Purchaser->ShippingAddress = new PurchaserAddress();
$request->Purchaser->ShippingAddress->Name = "Murat";
$request->Purchaser->ShippingAddress->SurName = "Kaya";
$request->Purchaser->ShippingAddress->Address = "Mevlüt Pehlivan Mah-> Multinet Plaza Şişli";
$request->Purchaser->ShippingAddress->ZipCode = "34782";
$request->Purchaser->ShippingAddress->CityCode = "34";
$request->Purchaser->ShippingAddress->IdentityNumber = "1234567890";
$request->Purchaser->ShippingAddress->CountryCode = "TR";
$request->Purchaser->ShippingAddress->PhoneNumber = "2122222222";
#endregion

#region Ürün bilgileri
$request->Products =  array();
$p = new Product();
$p->Title = "Telefon";
$p->Code = "TLF0001";
$p->Price = "5000";
$p->Quantity = 1;
$request->Products[0]=$p;

$p = new Product();
$p->Title = "Bilgisayar";
$p->Code = "BLG0001";
$p->Price = "5000";
$p->Quantity = 1;
$request->Products[1]=$p;

#endregion


$response=ThreeDPaymentCompleteRequest::execute($request,$settings); //3D secure 2. adımının başlatılması için gerekli servis çağrısını temsil eder.
print "<h1>3D Ödeme Başarılı</h1>";

print "<h3>Sonuç:</h3>";
echo ("<pre>");
$xml = simplexml_load_string($response, "SimpleXMLElement", LIBXML_NOCDATA);
$json = json_encode($xml);
$ipara3DpaymentResponse = json_decode($json,TRUE);
print_r($ipara3DpaymentResponse);
echo ("</pre>");
    
// Objelerde Tutulan Tüm Veriler Şu Şekilde Görüntülenip, Daha Kolay Veritabanı Kaydı Yapılabilir :
    
$urunBilgileri  = $request->Products;
$musteriBilgileri  = $request->Purchaser;
$teslimatBilgileri = $request->Purchaser->ShippingAddress;
    
echo "<h2>Ödeme Bilgileri</h2>";
print_r($paymentResponse);

echo "<h2>Ürün Bilgileri</h2>";
print_r($urunBilgileri);

echo "<h2>Müşteri Bilgileri</h2>";
print_r($musteriBilgileri);

echo "<h2>Teslimat Bilgileri</h2>";
print_r($teslimatBilgileri);
    
}
else 
{
    header('Location: fail.php');exit();
    
}
