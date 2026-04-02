' =============================================
' DatabaseContext.vb
' Capa de acceso a datos usando ADO.NET con MySQL
' Maneja todas las operaciones CRUD contra MySQL
' Requiere NuGet: MySql.Data
' =============================================
Imports MySql.Data.MySqlClient
Imports System.Security.Cryptography
Imports System.Text

Namespace CRM_VBNet.Models

    Public Class DatabaseContext
        Private ReadOnly _connectionString As String

        Public Sub New(connectionString As String)
            _connectionString = connectionString
        End Sub

        ' Obtiene una nueva conexión MySQL
        Private Function GetConnection() As MySqlConnection
            Return New MySqlConnection(_connectionString)
        End Function

#Region "Autenticación"

        ''' <summary>
        ''' Genera un salt aleatorio para hashing de contraseñas
        ''' </summary>
        Public Shared Function GenerateSalt() As String
            Dim saltBytes(31) As Byte
            Using rng = New RNGCryptoServiceProvider()
                rng.GetBytes(saltBytes)
            End Using
            Return Convert.ToBase64String(saltBytes)
        End Function

        ''' <summary>
        ''' Genera hash SHA-256 de la contraseña con salt
        ''' </summary>
        Public Shared Function HashPassword(password As String, salt As String) As String
            Using sha256 = SHA256.Create()
                Dim combined = Encoding.UTF8.GetBytes(password & salt)
                Dim hash = sha256.ComputeHash(combined)
                Return Convert.ToBase64String(hash)
            End Using
        End Function

        ''' <summary>
        ''' Autentica un usuario por username/email y contraseña
        ''' </summary>
        Public Function Authenticate(username As String, password As String) As User
            Using conn = GetConnection()
                conn.Open()
                Dim cmd As New MySqlCommand(
                    "SELECT Id, Username, Email, PasswordHash, Salt, FirstName, LastName,
                            Phone, WhatsApp, Address, PhotoPath, RoleId, IsActive
                     FROM Users
                     WHERE (Username = @Username OR Email = @Username) AND IsActive = 1",
                    conn)
                cmd.Parameters.AddWithValue("@Username", username)

                Using reader = cmd.ExecuteReader()
                    If reader.Read() Then
                        Dim salt = reader("Salt").ToString()
                        Dim storedHash = reader("PasswordHash").ToString()
                        Dim computedHash = HashPassword(password, salt)

                        If storedHash = computedHash Then
                            Dim user As New User() With {
                                .Id = CInt(reader("Id")),
                                .Username = reader("Username").ToString(),
                                .Email = reader("Email").ToString(),
                                .FirstName = reader("FirstName").ToString(),
                                .LastName = reader("LastName").ToString(),
                                .Phone = If(IsDBNull(reader("Phone")), "", reader("Phone").ToString()),
                                .WhatsApp = If(IsDBNull(reader("WhatsApp")), "", reader("WhatsApp").ToString()),
                                .Address = If(IsDBNull(reader("Address")), "", reader("Address").ToString()),
                                .PhotoPath = If(IsDBNull(reader("PhotoPath")), "", reader("PhotoPath").ToString()),
                                .RoleId = CInt(reader("RoleId"))
                            }

                            reader.Close()

                            ' Actualizar último login
                            Dim updateCmd As New MySqlCommand(
                                "UPDATE Users SET LastLogin = NOW() WHERE Id = @Id", conn)
                            updateCmd.Parameters.AddWithValue("@Id", user.Id)
                            updateCmd.ExecuteNonQuery()

                            ' Cargar el rol
                            user.Role = GetRoleById(user.RoleId)
                            Return user
                        End If
                    End If
                End Using
            End Using
            Return Nothing
        End Function

#End Region

#Region "Roles"

        Public Function GetRoleById(id As Integer) As Role
            Using conn = GetConnection()
                conn.Open()
                Dim cmd As New MySqlCommand(
                    "SELECT Id, Name, Description, CanRead, CanWrite, CanDelete FROM Roles WHERE Id = @Id", conn)
                cmd.Parameters.AddWithValue("@Id", id)

                Using reader = cmd.ExecuteReader()
                    If reader.Read() Then
                        Return New Role() With {
                            .Id = CInt(reader("Id")),
                            .Name = reader("Name").ToString(),
                            .Description = If(IsDBNull(reader("Description")), "", reader("Description").ToString()),
                            .CanRead = CBool(reader("CanRead")),
                            .CanWrite = CBool(reader("CanWrite")),
                            .CanDelete = CBool(reader("CanDelete"))
                        }
                    End If
                End Using
            End Using
            Return Nothing
        End Function

        Public Function GetAllRoles() As List(Of Role)
            Dim roles As New List(Of Role)()
            Using conn = GetConnection()
                conn.Open()
                Dim cmd As New MySqlCommand(
                    "SELECT Id, Name, Description, CanRead, CanWrite, CanDelete FROM Roles ORDER BY Id", conn)

                Using reader = cmd.ExecuteReader()
                    While reader.Read()
                        roles.Add(New Role() With {
                            .Id = CInt(reader("Id")),
                            .Name = reader("Name").ToString(),
                            .Description = If(IsDBNull(reader("Description")), "", reader("Description").ToString()),
                            .CanRead = CBool(reader("CanRead")),
                            .CanWrite = CBool(reader("CanWrite")),
                            .CanDelete = CBool(reader("CanDelete"))
                        })
                    End While
                End Using
            End Using
            Return roles
        End Function

#End Region

#Region "Usuarios - CRUD"

        Public Function GetAllUsers() As List(Of User)
            Dim users As New List(Of User)()
            Using conn = GetConnection()
                conn.Open()
                Dim cmd As New MySqlCommand(
                    "SELECT u.Id, u.Username, u.Email, u.FirstName, u.LastName, u.Phone,
                            u.WhatsApp, u.Address, u.PhotoPath, u.RoleId, u.IsActive,
                            u.LastLogin, u.CreatedAt, r.Name AS RoleName
                     FROM Users u
                     INNER JOIN Roles r ON u.RoleId = r.Id
                     ORDER BY u.CreatedAt DESC", conn)

                Using reader = cmd.ExecuteReader()
                    While reader.Read()
                        Dim user As New User() With {
                            .Id = CInt(reader("Id")),
                            .Username = reader("Username").ToString(),
                            .Email = reader("Email").ToString(),
                            .FirstName = reader("FirstName").ToString(),
                            .LastName = reader("LastName").ToString(),
                            .Phone = If(IsDBNull(reader("Phone")), "", reader("Phone").ToString()),
                            .WhatsApp = If(IsDBNull(reader("WhatsApp")), "", reader("WhatsApp").ToString()),
                            .Address = If(IsDBNull(reader("Address")), "", reader("Address").ToString()),
                            .PhotoPath = If(IsDBNull(reader("PhotoPath")), "", reader("PhotoPath").ToString()),
                            .RoleId = CInt(reader("RoleId")),
                            .IsActive = CBool(reader("IsActive")),
                            .LastLogin = If(IsDBNull(reader("LastLogin")), Nothing, CDate(reader("LastLogin"))),
                            .CreatedAt = CDate(reader("CreatedAt")),
                            .Role = New Role() With {.Name = reader("RoleName").ToString()}
                        }
                        users.Add(user)
                    End While
                End Using
            End Using
            Return users
        End Function

        Public Function GetUserById(id As Integer) As User
            Using conn = GetConnection()
                conn.Open()
                Dim cmd As New MySqlCommand(
                    "SELECT u.*, r.Name AS RoleName, r.CanRead, r.CanWrite, r.CanDelete
                     FROM Users u
                     INNER JOIN Roles r ON u.RoleId = r.Id
                     WHERE u.Id = @Id", conn)
                cmd.Parameters.AddWithValue("@Id", id)

                Using reader = cmd.ExecuteReader()
                    If reader.Read() Then
                        Return New User() With {
                            .Id = CInt(reader("Id")),
                            .Username = reader("Username").ToString(),
                            .Email = reader("Email").ToString(),
                            .FirstName = reader("FirstName").ToString(),
                            .LastName = reader("LastName").ToString(),
                            .Phone = If(IsDBNull(reader("Phone")), "", reader("Phone").ToString()),
                            .WhatsApp = If(IsDBNull(reader("WhatsApp")), "", reader("WhatsApp").ToString()),
                            .Address = If(IsDBNull(reader("Address")), "", reader("Address").ToString()),
                            .PhotoPath = If(IsDBNull(reader("PhotoPath")), "", reader("PhotoPath").ToString()),
                            .RoleId = CInt(reader("RoleId")),
                            .IsActive = CBool(reader("IsActive")),
                            .Role = New Role() With {
                                .Id = CInt(reader("RoleId")),
                                .Name = reader("RoleName").ToString(),
                                .CanRead = CBool(reader("CanRead")),
                                .CanWrite = CBool(reader("CanWrite")),
                                .CanDelete = CBool(reader("CanDelete"))
                            }
                        }
                    End If
                End Using
            End Using
            Return Nothing
        End Function

        Public Function CreateUser(user As User, password As String) As Integer
            Dim salt = GenerateSalt()
            Dim hash = HashPassword(password, salt)

            Using conn = GetConnection()
                conn.Open()
                Dim cmd As New MySqlCommand(
                    "INSERT INTO Users (Username, Email, PasswordHash, Salt, FirstName, LastName,
                                        Phone, WhatsApp, Address, RoleId)
                     VALUES (@Username, @Email, @Hash, @Salt, @FirstName, @LastName,
                             @Phone, @WhatsApp, @Address, @RoleId);
                     SELECT LAST_INSERT_ID();", conn)

                cmd.Parameters.AddWithValue("@Username", user.Username)
                cmd.Parameters.AddWithValue("@Email", user.Email)
                cmd.Parameters.AddWithValue("@Hash", hash)
                cmd.Parameters.AddWithValue("@Salt", salt)
                cmd.Parameters.AddWithValue("@FirstName", user.FirstName)
                cmd.Parameters.AddWithValue("@LastName", user.LastName)
                cmd.Parameters.AddWithValue("@Phone", If(String.IsNullOrEmpty(user.Phone), DBNull.Value, user.Phone))
                cmd.Parameters.AddWithValue("@WhatsApp", If(String.IsNullOrEmpty(user.WhatsApp), DBNull.Value, user.WhatsApp))
                cmd.Parameters.AddWithValue("@Address", If(String.IsNullOrEmpty(user.Address), DBNull.Value, user.Address))
                cmd.Parameters.AddWithValue("@RoleId", user.RoleId)

                Return CInt(cmd.ExecuteScalar())
            End Using
        End Function

        Public Function UpdateUser(user As User) As Boolean
            Using conn = GetConnection()
                conn.Open()
                Dim cmd As New MySqlCommand(
                    "UPDATE Users SET
                        FirstName = @FirstName, LastName = @LastName, Email = @Email,
                        Phone = @Phone, WhatsApp = @WhatsApp, Address = @Address,
                        RoleId = @RoleId, IsActive = @IsActive
                     WHERE Id = @Id", conn)

                cmd.Parameters.AddWithValue("@Id", user.Id)
                cmd.Parameters.AddWithValue("@FirstName", user.FirstName)
                cmd.Parameters.AddWithValue("@LastName", user.LastName)
                cmd.Parameters.AddWithValue("@Email", user.Email)
                cmd.Parameters.AddWithValue("@Phone", If(String.IsNullOrEmpty(user.Phone), DBNull.Value, user.Phone))
                cmd.Parameters.AddWithValue("@WhatsApp", If(String.IsNullOrEmpty(user.WhatsApp), DBNull.Value, user.WhatsApp))
                cmd.Parameters.AddWithValue("@Address", If(String.IsNullOrEmpty(user.Address), DBNull.Value, user.Address))
                cmd.Parameters.AddWithValue("@RoleId", user.RoleId)
                cmd.Parameters.AddWithValue("@IsActive", user.IsActive)

                Return cmd.ExecuteNonQuery() > 0
            End Using
        End Function

        Public Function DeleteUser(id As Integer) As Boolean
            Using conn = GetConnection()
                conn.Open()
                ' Soft delete - solo desactivar
                Dim cmd As New MySqlCommand(
                    "UPDATE Users SET IsActive = 0 WHERE Id = @Id", conn)
                cmd.Parameters.AddWithValue("@Id", id)
                Return cmd.ExecuteNonQuery() > 0
            End Using
        End Function

        Public Function UpdateUserPhoto(userId As Integer, photoPath As String) As Boolean
            Using conn = GetConnection()
                conn.Open()
                Dim cmd As New MySqlCommand(
                    "UPDATE Users SET PhotoPath = @PhotoPath WHERE Id = @Id", conn)
                cmd.Parameters.AddWithValue("@Id", userId)
                cmd.Parameters.AddWithValue("@PhotoPath", photoPath)
                Return cmd.ExecuteNonQuery() > 0
            End Using
        End Function

        Public Function UpdateProfile(profile As ProfileViewModel) As Boolean
            Using conn = GetConnection()
                conn.Open()
                Dim cmd As New MySqlCommand(
                    "UPDATE Users SET
                        FirstName = @FirstName, LastName = @LastName, Email = @Email,
                        Phone = @Phone, WhatsApp = @WhatsApp, Address = @Address
                     WHERE Id = @Id", conn)

                cmd.Parameters.AddWithValue("@Id", profile.Id)
                cmd.Parameters.AddWithValue("@FirstName", profile.FirstName)
                cmd.Parameters.AddWithValue("@LastName", profile.LastName)
                cmd.Parameters.AddWithValue("@Email", profile.Email)
                cmd.Parameters.AddWithValue("@Phone", If(String.IsNullOrEmpty(profile.Phone), DBNull.Value, profile.Phone))
                cmd.Parameters.AddWithValue("@WhatsApp", If(String.IsNullOrEmpty(profile.WhatsApp), DBNull.Value, profile.WhatsApp))
                cmd.Parameters.AddWithValue("@Address", If(String.IsNullOrEmpty(profile.Address), DBNull.Value, profile.Address))

                Return cmd.ExecuteNonQuery() > 0
            End Using
        End Function

        Public Function ChangePassword(userId As Integer, newPassword As String) As Boolean
            Dim salt = GenerateSalt()
            Dim hash = HashPassword(newPassword, salt)

            Using conn = GetConnection()
                conn.Open()
                Dim cmd As New MySqlCommand(
                    "UPDATE Users SET PasswordHash = @Hash, Salt = @Salt WHERE Id = @Id", conn)
                cmd.Parameters.AddWithValue("@Id", userId)
                cmd.Parameters.AddWithValue("@Hash", hash)
                cmd.Parameters.AddWithValue("@Salt", salt)
                Return cmd.ExecuteNonQuery() > 0
            End Using
        End Function

#End Region

#Region "Configuración del Sitio"

        Public Function GetSiteSettings() As SiteSettingsViewModel
            Dim settings As New SiteSettingsViewModel()
            Using conn = GetConnection()
                conn.Open()
                Dim cmd As New MySqlCommand("SELECT SettingKey, SettingValue FROM SiteSettings", conn)

                Using reader = cmd.ExecuteReader()
                    While reader.Read()
                        Dim key = reader("SettingKey").ToString()
                        Dim value = If(IsDBNull(reader("SettingValue")), "", reader("SettingValue").ToString())
                        Select Case key
                            Case "SiteName" : settings.SiteName = value
                            Case "LogoPath" : settings.LogoPath = value
                            Case "PrimaryColor" : settings.PrimaryColor = value
                            Case "CompanyEmail" : settings.CompanyEmail = value
                            Case "CompanyPhone" : settings.CompanyPhone = value
                        End Select
                    End While
                End Using
            End Using
            Return settings
        End Function

        Public Function UpdateSiteSetting(key As String, value As String, userId As Integer) As Boolean
            Using conn = GetConnection()
                conn.Open()
                Dim cmd As New MySqlCommand(
                    "UPDATE SiteSettings SET SettingValue = @Value, UpdatedBy = @UserId
                     WHERE SettingKey = @Key", conn)
                cmd.Parameters.AddWithValue("@Key", key)
                cmd.Parameters.AddWithValue("@Value", If(String.IsNullOrEmpty(value), DBNull.Value, value))
                cmd.Parameters.AddWithValue("@UserId", userId)
                Return cmd.ExecuteNonQuery() > 0
            End Using
        End Function

#End Region

#Region "Auditoría"

        Public Sub LogAction(userId As Integer, action As String, tableName As String,
                            Optional recordId As Integer? = Nothing, Optional details As String = "")
            Using conn = GetConnection()
                conn.Open()
                Dim cmd As New MySqlCommand(
                    "INSERT INTO AuditLog (UserId, Action, TableName, RecordId, Details)
                     VALUES (@UserId, @Action, @Table, @RecordId, @Details)", conn)
                cmd.Parameters.AddWithValue("@UserId", userId)
                cmd.Parameters.AddWithValue("@Action", action)
                cmd.Parameters.AddWithValue("@Table", tableName)
                cmd.Parameters.AddWithValue("@RecordId", If(recordId.HasValue, recordId.Value, DBNull.Value))
                cmd.Parameters.AddWithValue("@Details", If(String.IsNullOrEmpty(details), DBNull.Value, details))
                cmd.ExecuteNonQuery()
            End Using
        End Sub

#End Region

#Region "Inicialización"

        ''' <summary>
        ''' Crea el usuario admin por defecto si no existe
        ''' </summary>
        Public Sub SeedDefaultAdmin()
            Using conn = GetConnection()
                conn.Open()
                Dim cmd As New MySqlCommand("SELECT COUNT(*) FROM Users WHERE Username = 'admin'", conn)
                Dim count = CInt(cmd.ExecuteScalar())

                If count = 0 Then
                    Dim salt = GenerateSalt()
                    Dim hash = HashPassword("Admin123!", salt)

                    Dim insertCmd As New MySqlCommand(
                        "INSERT INTO Users (Username, Email, PasswordHash, Salt, FirstName, LastName, RoleId)
                         VALUES ('admin', 'admin@crm.com', @Hash, @Salt, 'Admin', 'Sistema', 1)", conn)
                    insertCmd.Parameters.AddWithValue("@Hash", hash)
                    insertCmd.Parameters.AddWithValue("@Salt", salt)
                    insertCmd.ExecuteNonQuery()
                End If
            End Using
        End Sub

#End Region

    End Class

End Namespace
