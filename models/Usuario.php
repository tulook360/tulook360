<?php
// models/Usuario.php


class UsuarioModelo {
    
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // ====================================================================
    // 1. BÚSQUEDAS Y VALIDACIONES (Login, Unicidad)
    // ====================================================================
    
    // Buscar usuario por correo (Login)
    public function buscarPorCorreo(string $correo): ?array {
        $sql = "SELECT * FROM tbl_usuario WHERE usu_correo = :c LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute([':c' => $correo]);
        $data = $st->fetch(PDO::FETCH_ASSOC);
        return $data ?: null;
    }

    // Buscar usuario por cédula (Validación registro)
    public function buscarPorCedula(string $cedula): ?array {
        $sql = "SELECT usu_id FROM tbl_usuario WHERE usu_cedula = :c LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute([':c' => $cedula]);
        $data = $st->fetch(PDO::FETCH_ASSOC);
        return $data ?: null;
    }

    // Verificar duplicados al EDITAR (Excluyendo al propio usuario)
    public function verificarDuplicadoExcluyendoId($campo, $valor, $idExcluir) {
        // Solo permitimos validar estos dos campos sensibles
        $camposPermitidos = ['usu_correo', 'usu_cedula'];
        if (!in_array($campo, $camposPermitidos)) return false;

        $sql = "SELECT usu_id FROM tbl_usuario WHERE $campo = :valor AND usu_id != :idExcluir LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':valor' => $valor, ':idExcluir' => $idExcluir]);
        
        // Retorna datos si encuentra OTRO usuario con ese valor
        return $stmt->fetch(); 
    }


    // Sumar un intento fallido y devolver cuántos lleva
    public function registrarIntentoFallido($idUsuario) {
        $sql = "UPDATE tbl_usuario SET usu_intentos = usu_intentos + 1 WHERE usu_id = :id";
        $this->pdo->prepare($sql)->execute([':id' => $idUsuario]);
        
        // Consultar cuántos intentos tiene ahora
        $stmt = $this->pdo->prepare("SELECT usu_intentos FROM tbl_usuario WHERE usu_id = :id");
        $stmt->execute([':id' => $idUsuario]);
        return (int) $stmt->fetchColumn();
    }

    // Cambiar estado del usuario a Bloqueado ('B')
    public function bloquearUsuario($idUsuario) {
        $sql = "UPDATE tbl_usuario SET usu_estado = 'B' WHERE usu_id = :id";
        return $this->pdo->prepare($sql)->execute([':id' => $idUsuario]);
    }

    // Poner los intentos en 0 si logra iniciar sesión correctamente
    public function resetearIntentos($idUsuario) {
        $sql = "UPDATE tbl_usuario SET usu_intentos = 0 WHERE usu_id = :id";
        return $this->pdo->prepare($sql)->execute([':id' => $idUsuario]);
    }


    // ====================================================================
    // 2. PERFIL DE USUARIO (Lectura y Edición Personal)
    // ====================================================================

    // Obtener datos completos del usuario logueado
    public function obtenerPerfil($idUsuario) {
        $sql = "SELECT 
                    u.*, 
                    r.rol_nombre,
                    n.neg_nombre
                FROM tbl_usuario u
                INNER JOIN tbl_rol r ON u.rol_id = r.rol_id
                LEFT JOIN tbl_negocio n ON u.neg_id = n.neg_id
                WHERE u.usu_id = :id 
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $idUsuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar URL de la foto (Cloudinary)
    public function actualizarFotoPerfil($idUsuario, $nuevaUrl) {
        $sql = "UPDATE tbl_usuario SET usu_foto = :foto WHERE usu_id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':foto' => $nuevaUrl,
            ':id'   => $idUsuario
        ]);
    }

    // Actualizar un campo específico (Edición rápida)
    public function actualizarCampo($idUsuario, $campo, $valor) {
        $camposPermitidos = ['usu_nombres', 'usu_apellidos', 'usu_correo', 'usu_telefono'];

        if (!in_array($campo, $camposPermitidos)) {
            throw new Exception("El campo '$campo' no es editable.");
        }

        $sql = "UPDATE tbl_usuario SET $campo = :valor WHERE usu_id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':valor' => $valor,
            ':id'    => $idUsuario
        ]);
    }

    // Actualizar contraseña encriptada
    public function actualizarContrasena($idUsuario, $hash) {
        $sql = "UPDATE tbl_usuario SET usu_contrasena = :hash WHERE usu_id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':hash' => $hash, ':id' => $idUsuario]);
    }


    // ====================================================================
    // 3. GESTIÓN DE EMPLEADOS (CRUD del Admin de Negocio)
    // ====================================================================

    // ====================================================================
    // LISTAR USUARIOS (CON FILTRO DE BÚSQUEDA CORREGIDO)
    // ====================================================================
    public function listarPorNegocio($negocioId, $estado = 'A', $busqueda = '') {
        // AGREGAMOS: LEFT JOIN con tbl_sucursal para saber dónde están
        $sql = "SELECT u.*, r.rol_nombre, s.suc_nombre 
                FROM tbl_usuario u
                INNER JOIN tbl_rol r ON u.rol_id = r.rol_id
                LEFT JOIN tbl_sucursal s ON u.suc_id = s.suc_id
                WHERE u.neg_id = :negId AND u.usu_estado = :estado";

        $params = [':negId' => $negocioId, ':estado' => $estado];

        if (!empty($busqueda)) {
            $sql .= " AND (u.usu_cedula LIKE :b1 OR u.usu_nombres LIKE :b2 OR u.usu_apellidos LIKE :b3)";
            $termino = "%$busqueda%";
            $params[':b1'] = $termino; $params[':b2'] = $termino; $params[':b3'] = $termino;
        }

        $sql .= " ORDER BY u.usu_nombres ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener un empleado específico validando que sea de MI negocio
    public function obtenerPorIdYNegocio($id, $negocioId) {
        $sql = "SELECT * FROM tbl_usuario WHERE usu_id = :id AND neg_id = :negId LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id, ':negId' => $negocioId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    

    // Eliminar Lógico (Desactivar)
    public function eliminarLogico($id, $negocioId) {
        $sql = "UPDATE tbl_usuario SET usu_estado = 'I' WHERE usu_id = :id AND neg_id = :negId";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id, ':negId' => $negocioId]);
    }

    // Reactivar Empleado
    public function reactivar($id, $negocioId) {
        $sql = "UPDATE tbl_usuario SET usu_estado = 'A' WHERE usu_id = :id AND neg_id = :negId";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id, ':negId' => $negocioId]);
    }



    // NUEVO: Obtener TODO el perfil (Datos + Habilidades + Días Laborables)
    public function obtenerPerfilCompleto($idUsuario, $negocioId) {
        // 1. Datos Básicos
        $sql = "SELECT * FROM tbl_usuario WHERE usu_id = :id AND neg_id = :negId LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $idUsuario, ':negId' => $negocioId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) return null;

        // 2. Habilidades (IDs de categorías que domina)
        $stmtHab = $this->pdo->prepare("SELECT tser_id FROM tbl_empleado_habilidad WHERE usu_id = :id");
        $stmtHab->execute([':id' => $idUsuario]);
        $habilidades = $stmtHab->fetchAll(PDO::FETCH_COLUMN);

        // 3. Días Laborables
        $stmtHor = $this->pdo->prepare("SELECT hor_dia FROM tbl_empleado_horario WHERE usu_id = :id");
        $stmtHor->execute([':id' => $idUsuario]);
        $diasLaborables = $stmtHor->fetchAll(PDO::FETCH_COLUMN);

        return [
            'info' => $usuario,
            'habilidades' => $habilidades,
            'dias' => $diasLaborables
        ];
    }

    // NUEVO: Crear Empleado con TODO (Transacción Unificada)
    public function crearEmpleadoCompleto($datos) {
        try {
            $this->pdo->beginTransaction();

            // 1. Insertar Usuario Base
            $sql = "INSERT INTO tbl_usuario 
                    (neg_id, rol_id, suc_id, usu_cedula, usu_nombres, usu_apellidos, usu_correo, usu_contrasena, 
                     usu_sueldo_base, usu_comision_porcentaje, usu_nivel, usu_estado, usu_fecha_reg) 
                    VALUES 
                    (:neg, :rol, :suc, :ced, :nom, :ape, :mail, :pass, :sueldo, :comision, :nivel, 'A', NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':neg'      => $datos['neg_id'],
                ':rol'      => $datos['rol_id'],
                ':suc'      => !empty($datos['suc_id']) ? $datos['suc_id'] : null,
                ':ced'      => $datos['cedula'],
                ':nom'      => $datos['nombres'],
                ':ape'      => $datos['apellidos'],
                ':mail'     => $datos['correo'],
                ':pass'     => $datos['password'],
                ':sueldo'   => $datos['sueldo'] ?? 0,
                ':comision' => $datos['comision'] ?? 0,
                ':nivel'    => $datos['nivel'] ?? 'Junior'
            ]);

            $nuevoId = $this->pdo->lastInsertId();

            // 2. Insertar Historial de Sucursal (Si se asignó una)
            if (!empty($datos['suc_id'])) {
                $sqlHist = "INSERT INTO tbl_empleado_historial_suc (usu_id, suc_id, hist_fecha_inicio, hist_motivo) 
                            VALUES (:uid, :suc, NOW(), 'Contratación Inicial')";
                $this->pdo->prepare($sqlHist)->execute([':uid' => $nuevoId, ':suc' => $datos['suc_id']]);
            }

            // 3. Insertar Habilidades (Familias de servicio)
            if (!empty($datos['habilidades']) && is_array($datos['habilidades'])) {
                $sqlHab = "INSERT INTO tbl_empleado_habilidad (usu_id, tser_id) VALUES (:uid, :tid)";
                $stmtHab = $this->pdo->prepare($sqlHab);
                foreach ($datos['habilidades'] as $tserId) {
                    $stmtHab->execute([':uid' => $nuevoId, ':tid' => $tserId]);
                }
            }

            // 4. Insertar Horario (Días seleccionados)
            if (!empty($datos['dias_trabajo']) && is_array($datos['dias_trabajo'])) {
                // Obtenemos horario de la sucursal para copiar las horas por defecto
                $apertura = '09:00:00'; $cierre = '18:00:00';
                if (!empty($datos['suc_id'])) {
                    $stmtSuc = $this->pdo->prepare("SELECT sh_apertura, sh_cierre FROM tbl_sucursal_horario WHERE suc_id = :s AND sh_dia = 'Lunes' LIMIT 1");
                    $stmtSuc->execute([':s' => $datos['suc_id']]);
                    $horarioSuc = $stmtSuc->fetch(PDO::FETCH_ASSOC);
                    if($horarioSuc) {
                        $apertura = $horarioSuc['sh_apertura'];
                        $cierre = $horarioSuc['sh_cierre'];
                    }
                }

                $sqlHor = "INSERT INTO tbl_empleado_horario (usu_id, suc_id, hor_dia, hor_ini, hor_fin) VALUES (:uid, :suc, :dia, :ini, :fin)";
                $stmtHor = $this->pdo->prepare($sqlHor);
                foreach ($datos['dias_trabajo'] as $dia) {
                    $stmtHor->execute([
                        ':uid' => $nuevoId,
                        ':suc' => $datos['suc_id'],
                        ':dia' => $dia,
                        ':ini' => $apertura,
                        ':fin' => $cierre
                    ]);
                }
            }

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // ACTUALIZAR EMPLEADO COMPLETO (Transacción)
    public function actualizarEmpleadoCompleto($id, $datos) {
        try {
            $this->pdo->beginTransaction();

            // 1. Actualizar Usuario Base (Datos + Económicos + Sucursal)
            // Nota: La contraseña solo se actualiza si viene llena
            $sql = "UPDATE tbl_usuario SET 
                        suc_id = :suc,
                        usu_cedula = :ced,
                        usu_nombres = :nom,
                        usu_apellidos = :ape,
                        usu_correo = :mail,
                        rol_id = :rol,
                        usu_sueldo_base = :sueldo,
                        usu_comision_porcentaje = :comision,
                        usu_nivel = :nivel
                    WHERE usu_id = :id AND neg_id = :neg";
            
            $params = [
                ':suc'      => !empty($datos['suc_id']) ? $datos['suc_id'] : null,
                ':ced'      => $datos['cedula'],
                ':nom'      => $datos['nombres'],
                ':ape'      => $datos['apellidos'],
                ':mail'     => $datos['correo'],
                ':rol'      => $datos['rol_id'],
                ':sueldo'   => $datos['sueldo'] ?? 0,
                ':comision' => $datos['comision'] ?? 0,
                ':nivel'    => $datos['nivel'] ?? 'Junior',
                ':id'       => $id,
                ':neg'      => $datos['neg_id']
            ];

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            // 1.1 Si hay contraseña nueva, actualizarla aparte
            if (!empty($datos['password'])) {
                $sqlPass = "UPDATE tbl_usuario SET usu_contrasena = :pass WHERE usu_id = :id";
                $this->pdo->prepare($sqlPass)->execute([':pass' => $datos['password'], ':id' => $id]);
            }

            // 2. Historial Sucursal (Solo si cambió de sucursal real)
            // (Para simplificar, asumimos que si envía suc_id, verificamos si es diferente antes en lógica de negocio o simplemente registramos el cambio si es necesario. 
            // Por ahora, actualizamos el historial solo si no tiene uno abierto en esa sucursal, pero dejémoslo simple: el update de arriba ya mueve al empleado).

            // 3. Actualizar Habilidades (Borrar viejas -> Insertar nuevas)
            $this->pdo->prepare("DELETE FROM tbl_empleado_habilidad WHERE usu_id = :id")->execute([':id' => $id]);
            
            if (!empty($datos['habilidades']) && is_array($datos['habilidades'])) {
                $sqlHab = "INSERT INTO tbl_empleado_habilidad (usu_id, tser_id) VALUES (:uid, :tid)";
                $stmtHab = $this->pdo->prepare($sqlHab);
                foreach ($datos['habilidades'] as $tserId) {
                    $stmtHab->execute([':uid' => $id, ':tid' => $tserId]);
                }
            }

            // 4. Actualizar Horario (Borrar viejos -> Insertar nuevos)
            $this->pdo->prepare("DELETE FROM tbl_empleado_horario WHERE usu_id = :id")->execute([':id' => $id]);

            if (!empty($datos['dias_trabajo']) && is_array($datos['dias_trabajo'])) {
                // Obtener horas de la sucursal actual (o default)
                $apertura = '09:00:00'; $cierre = '18:00:00';
                if (!empty($datos['suc_id'])) {
                    $stmtSuc = $this->pdo->prepare("SELECT sh_apertura, sh_cierre FROM tbl_sucursal_horario WHERE suc_id = :s AND sh_dia = 'Lunes' LIMIT 1");
                    $stmtSuc->execute([':s' => $datos['suc_id']]);
                    $horarioSuc = $stmtSuc->fetch(PDO::FETCH_ASSOC);
                    if($horarioSuc) {
                        $apertura = $horarioSuc['sh_apertura'];
                        $cierre = $horarioSuc['sh_cierre'];
                    }
                }

                $sqlHor = "INSERT INTO tbl_empleado_horario (usu_id, suc_id, hor_dia, hor_ini, hor_fin) VALUES (:uid, :suc, :dia, :ini, :fin)";
                $stmtHor = $this->pdo->prepare($sqlHor);
                foreach ($datos['dias_trabajo'] as $dia) {
                    $stmtHor->execute([
                        ':uid' => $id,
                        ':suc' => $datos['suc_id'],
                        ':dia' => $dia,
                        ':ini' => $apertura,
                        ':fin' => $cierre
                    ]);
                }
            }

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // ====================================================================
    // 4. REGISTRO DE CLIENTE (App Pública)
    // ====================================================================
    
    // Guardar nuevo cliente (Auto-registro)
    public function guardar($datos) {
        try {
            $sql = "INSERT INTO tbl_usuario (
                        neg_id, 
                        rol_id, 
                        suc_id, 
                        usu_cedula, 
                        usu_nombres, 
                        usu_apellidos, 
                        usu_correo, 
                        usu_contrasena, 
                        usu_foto, 
                        usu_fecha_reg, 
                        usu_estado, 
                        usu_intentos
                    ) VALUES (
                        :neg, 
                        :rol, 
                        :suc, 
                        :ced, 
                        :nom, 
                        :ape, 
                        :cor, 
                        :pass, 
                        :foto, 
                        :fec, 
                        'A', 
                        0
                    )";

            $stmt = $this->pdo->prepare($sql);
            
            // Ejecutar con los datos mapeados desde el Controlador
            return $stmt->execute([
                ':neg'  => $datos['neg_id'] ?? null, 
                ':rol'  => $datos['rol_id'],
                ':suc'  => $datos['suc_id'] ?? null,
                ':ced'  => $datos['cedula'],
                ':nom'  => $datos['nombres'],
                ':ape'  => $datos['apellidos'],
                ':cor'  => $datos['correo'],
                ':pass' => $datos['password'], // La contraseña ya viene encriptada
                ':foto' => $datos['foto'] ?? null,
                ':fec'  => $datos['fecha_reg']
            ]);

        } catch (PDOException $e) {
            // Si hay error (ej: duplicado que se pasó el filtro), retornamos false
            return false;
        }
    }


    // ====================================================================
    // RECUPERACIÓN DE CONTRASEÑA Y DESBLOQUEO
    // ====================================================================

    public function crearTokenRecuperacion($idUsuario, $token) {
        // LO PONEMOS AQUÍ ADENTRO
        require_once __DIR__ . '/../nucleo/TimeHelper.php';
        
        $expira = TimeHelper::addMinutes(60); 
        
        $sql = "INSERT INTO tbl_recuperacion (usu_id, rec_token, rec_expira, rec_estado) 
                VALUES (:uid, :tok, :exp, 'A')";
                
        return $this->pdo->prepare($sql)->execute([
            ':uid' => $idUsuario, 
            ':tok' => $token, 
            ':exp' => $expira
        ]);
    }

    public function validarToken($token) {
        // Y LO PONEMOS AQUÍ ADENTRO TAMBIÉN
        require_once __DIR__ . '/../nucleo/TimeHelper.php';
        
        $ahoraEcuador = TimeHelper::now();
        
        $sql = "SELECT r.*, u.usu_correo, u.usu_nombres 
                FROM tbl_recuperacion r
                INNER JOIN tbl_usuario u ON r.usu_id = u.usu_id
                WHERE r.rec_token = :tok 
                  AND r.rec_estado = 'A' 
                  AND r.rec_expira > :ahora 
                LIMIT 1";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tok' => $token,
            ':ahora' => $ahoraEcuador
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function aplicarRecuperacionYDesbloqueo($idUsuario, $hashNuevaClave, $token) {
        try {
            $this->pdo->beginTransaction();
            // Cambia clave, activa cuenta ('A') y resetea intentos
            $sql1 = "UPDATE tbl_usuario SET usu_contrasena = :hash, usu_estado = 'A', usu_intentos = 0 WHERE usu_id = :uid";
            $this->pdo->prepare($sql1)->execute([':hash' => $hashNuevaClave, ':uid' => $idUsuario]);

            // Quema el token ('U' de Usado)
            $sql2 = "UPDATE tbl_recuperacion SET rec_estado = 'U' WHERE rec_token = :tok";
            $this->pdo->prepare($sql2)->execute([':tok' => $token]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
    
}