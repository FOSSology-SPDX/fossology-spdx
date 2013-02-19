CREATE TABLE spdx_file (
    spdx_pk				integer,  -- Primary Key
    version				text NOT NULL,
    data_license		text NOT NULL,
	  document_comment	text,
	  creator				text NOT NULL,
    creator_optional1   text,
    creator_optional2   text,
    created_date		timestamp NOT NULL,
	  creator_comment	text,
	  verificationcode	text NOT NULL,
    CONSTRAINT spdx_file_pk PRIMARY KEY (spdx_pk)
);