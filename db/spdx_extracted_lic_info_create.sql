CREATE TABLE spdx_extracted_lic_info (
    identifier			integer NOT NULL,
    licensename			text NOT NULL,
    cross_ref_url		text,
    lic_comment		text,
    spdx_fk				integer NOT NULL,
    CONSTRAINT spdx_extracted_lic_info_pk PRIMARY KEY (identifier,spdx_fk)
);