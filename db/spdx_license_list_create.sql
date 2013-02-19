CREATE TABLE spdx_license_list (
    license_list_pk        integer,  -- Primary Key
    license_identifier       text NOT NULL,
    license_fullname         text NOT NULL,
    license_matchname_1   text,
    license_matchname_2        text,
    license_matchname_3         text,
    CONSTRAINT spdx_license_list_pk PRIMARY KEY (license_list_pk)
);