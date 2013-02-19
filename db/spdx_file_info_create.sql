CREATE TABLE spdx_file_info (
    file_info_pk		bigserial,  -- Primary Key
    filename			text,
    filetype			text,
	checksum			text,
	license_concluded	text NOT NULL,
	license_info_in_file	text,
	license_comment	text,
	file_copyright_text	text,
	artifact_of_project	text,
	artifact_of_homepage	text,
	artifact_of_url		text,
	file_comment		text,
	package_info_fk		integer NOT NULL,
	spdx_fk				integer NOT NULL,
    CONSTRAINT spdx_file_info_pk PRIMARY KEY (file_info_pk)
);