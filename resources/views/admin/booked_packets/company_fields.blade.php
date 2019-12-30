{!! Form::hidden('company_name_eng', $data['company']['company_name_eng'], ['id' => 'company_name_eng']) !!}
{!! Form::hidden('company_address1_eng', $data['company']['company_address1_eng'], ['id' => 'company_address1_eng']) !!}
{!! Form::hidden('company_phone', $data['company']['company_phone'], ['id' => 'company_phone']) !!}
{!! Form::hidden('company_email', $data['company']['company_email'], ['id' => 'company_email']) !!}
{!! Form::hidden('company_origin_city', $data['company']['tbl_lcs_city_city_id'], ['id' => 'company_origin_city']) !!}


<!-- Other Company Fields -->
{!! Form::hidden('other_company_name_eng', $data['booked_packet']['shipper_name'], ['id' => 'other_company_name_eng']) !!}
{!! Form::hidden('other_company_address1_eng', $data['booked_packet']['shipper_address'], ['id' => 'other_company_address1_eng']) !!}
{!! Form::hidden('other_company_phone', $data['booked_packet']['shipper_phone'], ['id' => 'other_company_phone']) !!}
{!! Form::hidden('other_company_email', $data['booked_packet']['shipper_email'], ['id' => 'other_company_email']) !!}
{!! Form::hidden('other_company_origin_city', $data['booked_packet']['origin_city'], ['id' => 'other_company_origin_city']) !!}