CREATE TABLE spdx_review_ref (
    review_pk		integer,  -- Primary Key
    reviewer		text NOT NULL,
    review_date		timestamp NOT NULL,
    review_comment	text,
	spdx_fk			integer NOT NULL,
    CONSTRAINT spdx_review_ref_pk PRIMARY KEY (review_pk)
);