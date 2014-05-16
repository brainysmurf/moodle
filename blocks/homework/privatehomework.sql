ALTER TABLE ssismdl_block_homework ADD COLUMN private smallint;
ALTER TABLE ssismdl_block_homework ALTER COLUMN private SET NOT NULL;
ALTER TABLE ssismdl_block_homework ALTER COLUMN private SET DEFAULT 0;

CREATE INDEX ssismdl_blochome_pri_ix
  ON ssismdl_block_homework
  USING btree
  (private);
