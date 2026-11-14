-- Contrasena de todos los usuarios de prueba: 1234

INSERT INTO Usuario (Nombre, Correo, Contrasena, Rol) VALUES
('Pablo Urbina', 'lider@winni.com', '$2y$10$v1yn6p8irOOVEkk/KJVST.NFQuZOhvxcASwbNNfvYdrx97F0Y0pTO', 'Lider'),
('Angel Camargo', 'angel@winni.com', '$2y$10$v1yn6p8irOOVEkk/KJVST.NFQuZOhvxcASwbNNfvYdrx97F0Y0pTO', 'Colaborador'),
('David Ortega', 'david@winni.com', '$2y$10$v1yn6p8irOOVEkk/KJVST.NFQuZOhvxcASwbNNfvYdrx97F0Y0pTO', 'Colaborador'),
('Maria Lopez', 'maria@winni.com', '$2y$10$v1yn6p8irOOVEkk/KJVST.NFQuZOhvxcASwbNNfvYdrx97F0Y0pTO', 'Colaborador');

INSERT INTO Proyecto (Nombre, Descripcion, ID_lider) VALUES
('Sistema de Inventario', 'Aplicacion web para control de inventario', 1),
('App Movil', 'Prototipo de aplicacion movil', 1);

INSERT INTO Miembro VALUES (1, 2), (1, 3), (1, 4), (2, 2);

INSERT INTO Fase (Nombre, Orden, Fecha_inicio, Fecha_limite, ID_proyecto) VALUES
('Analisis', 1, CURRENT_DATE - 20, CURRENT_DATE - 5, 1),
('Backend', 2, CURRENT_DATE - 4, CURRENT_DATE + 10, 1),
('Frontend', 3, CURRENT_DATE + 11, CURRENT_DATE + 25, 1),
('Diseno', 1, CURRENT_DATE, CURRENT_DATE + 14, 2);

INSERT INTO Tarea (Titulo, Descripcion, Prioridad, Estado, Fecha_limite, ID_fase) VALUES
('Requerimientos', 'Levantar requerimientos con el cliente', 'Alta', 'Completado', NOW() - INTERVAL '10 days', 1),
('Diagrama ER', 'Diagrama entidad relacion de la base de datos', 'Alta', 'Completado', NOW() - INTERVAL '6 days', 1),
('Crear tablas', 'Script SQL de creacion', 'Alta', 'En revision', NOW() - INTERVAL '1 day', 2),
('Login', 'Inicio de sesion y registro', 'Media', 'En progreso', NOW() + INTERVAL '1 day', 2),
('CRUD productos', 'Agregar, editar y eliminar productos', 'Media', 'Por hacer', NOW() + INTERVAL '7 days', 2),
('Estilos', 'Hoja de estilos general', 'Baja', 'Por hacer', NOW() + INTERVAL '20 days', 3),
('Mockups', 'Pantallas principales', 'Alta', 'En progreso', NOW() + INTERVAL '5 days', 4);

INSERT INTO Asignacion VALUES (1, 2), (2, 3), (3, 3), (3, 4), (4, 2), (5, 4), (6, 2), (7, 2);

INSERT INTO Bitacora (Accion, ID_usuario, ID_proyecto) VALUES
('creo el proyecto Sistema de Inventario', 1, 1),
('creo el proyecto App Movil', 1, 2),
('cambio la tarea Requerimientos a Completado', 1, 1);

INSERT INTO Comentario (Texto, ID_tarea, ID_usuario) VALUES
('Ya subi el script, falta revisar las llaves foraneas', 3, 3),
('Revisen el ON DELETE de Asignacion', 3, 1);
