CREATE TABLE "[{table}]" (
  "id" NUMBER GENERATED ALWAYS AS IDENTITY,
  "timestamp" timestamp,
  "priority" int,
  "name" varchar,
  "message" text,
  PRIMARY KEY ("id")
) ;
