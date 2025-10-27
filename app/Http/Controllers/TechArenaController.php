<?php

namespace App\Http\Controllers;

use App\Models\Exhibitors;
use App\Services\ElmiaService;
use Illuminate\Support\Arr;

class TechArenaController extends Controller
{
    public function __construct(protected ElmiaService $elmiaService)
    {
    }

    public function index()
    {
        $exhibitors = Exhibitors::get();

        foreach ($exhibitors as $exhibitor) {
            $data = $this->elmiaService->getExhibitorDetails($exhibitor->exhibitor_id, $exhibitor->stand_id);

            Exhibitors::updateOrCreate(
                [
                    'exhibitor_id' => $data['exhibitorId'],
                    'stand_id'     => $data['standId'],
                ],
                [
                    'company_name'      => $data['companyName'] ?? null,
                    'company_email'     => $data['companyEmail'] ?? null,
                    'company_phone'     => $data['companyPhone'] ?? null,
                    'company_fax'       => $data['companyFax'] ?? null,
                    'company_logo'      => $data['companyLogo'] ?? null,
                    'company_facebook'  => $data['companyFacebook'] ?? null,
                    'company_instagram' => $data['companyInstagram'] ?? null,
                    'company_linkedin'  => $data['companyLinkedIn'] ?? null,
                    'company_youtube'   => $data['companyYoutube'] ?? null,

                    'address1' => $data['address1'] ?? null,
                    'address2' => $data['address2'] ?? null,
                    'address3' => $data['address3'] ?? null,
                    'city'     => $data['city'] ?? null,
                    'postal'   => $data['postal'] ?? null,
                    'country'  => $data['country'] ?? null,

                    'invoice_company_name' => $data['invoiceCompanyName'] ?? null,
                    'invoice_email'        => $data['invoiceEmail'] ?? null,
                    'invoice_address1'     => $data['invoiceAddress1'] ?? null,
                    'invoice_address2'     => $data['invoiceAddress2'] ?? null,
                    'invoice_iso_code'     => $data['invoiceIsoCode'] ?? null,
                    'invoice_postal'       => $data['invoicePostal'] ?? null,

                    'stand_id'   => $data['standId'] ?? null,
                    'stand_nr'   => $data['standNr'] ?? null,
                    'stand_link' => $data['standLink'] ?? null,

                    'project_id'      => $data['projectId'] ?? null,
                    'project_name'    => $data['projectName'] ?? null,
                    'project_name_en' => $data['projectNameEn'] ?? null,
                    'project_name_sv' => $data['projectNameSv'] ?? null,

                    'fair_catalog_text'      => $data['fairCatalogText'] ?? null,
                    'fair_catalogue_text_en' => $data['fairCatalogueTextEn'] ?? null,
                    'fair_catalogue_text_sv' => $data['fairCatalogueTextSv'] ?? null,

                    'meeting_reservation_link' => $data['meetingReservationLink'] ?? null,
                    'organisation_number'      => $data['organisationNumber'] ?? null,
                    'url'                      => $data['url'] ?? null,

                    // JSON-поля
                    'products'                 => json_encode($data['products'] ?? []),
                    'themes'                   => json_encode($data['themes'] ?? []),
                ],
            );

            usleep(500000);
        }
    }

    public function createCompany()
    {
        $companies = $this->elmiaService->getCompanies(25130);


        foreach ($companies as $company) {
            Exhibitors::updateOrCreate(
                [
                    'exhibitor_id' => $company['exhibitorId'],
                    'stand_id'     => $company['stand']['standId'],
                ],
                [],
            );
        }
    }
}
