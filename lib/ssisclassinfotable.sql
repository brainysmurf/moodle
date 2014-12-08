-- Table: ssismdl_ssis_class_info

-- DROP TABLE ssismdl_ssis_class_info;

CREATE TABLE ssismdl_ssis_class_info
(
  id bigserial NOT NULL,
  courseid bigint NOT NULL DEFAULT 0,
  teacheruserid bigint NOT NULL DEFAULT 0,
  name character varying(255) NOT NULL DEFAULT ''::character varying,
  comment character varying(255) NOT NULL DEFAULT ''::character varying,
  CONSTRAINT ssismdl_ssis_class_pk PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE ssismdl_ssis_class_info
  OWNER TO moodle3;
COMMENT ON TABLE ssismdl_ssis_class_info
  IS 'Defines groups/classes, filled in by psmdlsyncer';

-- Index: ssismdl_ssis_cla2_ix

-- DROP INDEX ssismdl_ssis_cla2_ix;

CREATE INDEX ssismdl_ssis_cla2_ix
  ON ssismdl_ssis_class_info
  USING btree
  (courseid);


