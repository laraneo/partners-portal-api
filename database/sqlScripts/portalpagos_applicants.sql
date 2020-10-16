
CREATE TABLE [dbo].[portalpagos_applicants](
[id] [int] IDENTITY(1,1) NOT NULL,
[sCI] [varchar](50) NOT NULL,
[sNombres] [varchar](255) NOT NULL,
[sApellidos] [varchar](255) NOT NULL,
[sArchivo] [varchar](255) NOT NULL,
[nStatus] [int] NOT NULL,
[dCreated] [datetime] NOT NULL,
 CONSTRAINT [PK_portalpagos_applicants] PRIMARY KEY CLUSTERED
(
[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]


ALTER TABLE portalpagos_applicants ADD picture VARCHAR(255) NULL;