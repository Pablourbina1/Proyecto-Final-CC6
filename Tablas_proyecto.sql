create table Usuario (
    ID_usuario SERIAL PRIMARY KEY,
    Nombre VARCHAR(50) NOT NULL,
    Correo VARCHAR(80) UNIQUE NOT NULL,
    Contrasena VARCHAR(255) NOT NULL,
    Rol VARCHAR(12) NOT NULL CHECK (Rol IN ('Lider', 'Colaborador'))
);

create table Proyecto (
    ID_proyecto SERIAL PRIMARY KEY,
    Nombre VARCHAR(60) NOT NULL,
    Descripcion VARCHAR(300),
    ID_lider INT NOT NULL,
    FOREIGN KEY (ID_lider) REFERENCES Usuario(ID_usuario)
);

create table Miembro (
    ID_proyecto INT,
    ID_usuario INT,
    PRIMARY KEY (ID_proyecto, ID_usuario),
    FOREIGN KEY (ID_proyecto) REFERENCES Proyecto(ID_proyecto) ON DELETE CASCADE,
    FOREIGN KEY (ID_usuario) REFERENCES Usuario(ID_usuario)
);

create table Fase (
    ID_fase SERIAL PRIMARY KEY,
    Nombre VARCHAR(50) NOT NULL,
    Orden INT NOT NULL,
    Fecha_inicio DATE NOT NULL,
    Fecha_limite DATE NOT NULL,
    ID_proyecto INT NOT NULL,
    CHECK (Fecha_limite >= Fecha_inicio),
    FOREIGN KEY (ID_proyecto) REFERENCES Proyecto(ID_proyecto) ON DELETE CASCADE
);

create table Tarea (
    ID_tarea SERIAL PRIMARY KEY,
    Titulo VARCHAR(80) NOT NULL,
    Descripcion VARCHAR(500),
    Prioridad VARCHAR(5) NOT NULL CHECK (Prioridad IN ('Alta', 'Media', 'Baja')),
    Estado VARCHAR(12) NOT NULL DEFAULT 'Por hacer'
        CHECK (Estado IN ('Por hacer', 'En progreso', 'En revision', 'Completado')),
    Fecha_limite TIMESTAMP NOT NULL,
    Link_evidencia VARCHAR(200),
    ID_fase INT NOT NULL,
    FOREIGN KEY (ID_fase) REFERENCES Fase(ID_fase) ON DELETE CASCADE
);

create table Asignacion (
    ID_tarea INT,
    ID_usuario INT,
    PRIMARY KEY (ID_tarea, ID_usuario),
    FOREIGN KEY (ID_tarea) REFERENCES Tarea(ID_tarea) ON DELETE CASCADE,
    FOREIGN KEY (ID_usuario) REFERENCES Usuario(ID_usuario)
);

create table Bitacora (
    ID_bitacora SERIAL PRIMARY KEY,
    Fecha TIMESTAMP DEFAULT NOW(),
    Accion VARCHAR(200) NOT NULL,
    ID_usuario INT NOT NULL,
    ID_proyecto INT NOT NULL,
    FOREIGN KEY (ID_usuario) REFERENCES Usuario(ID_usuario),
    FOREIGN KEY (ID_proyecto) REFERENCES Proyecto(ID_proyecto) ON DELETE CASCADE
);

create table Comentario (
    ID_comentario SERIAL PRIMARY KEY,
    Texto VARCHAR(500) NOT NULL,
    Fecha TIMESTAMP DEFAULT NOW(),
    ID_tarea INT NOT NULL,
    ID_usuario INT NOT NULL,
    FOREIGN KEY (ID_tarea) REFERENCES Tarea(ID_tarea) ON DELETE CASCADE,
    FOREIGN KEY (ID_usuario) REFERENCES Usuario(ID_usuario)
);
