' =============================================
' PermissionHelper.vb
' Valida permisos del usuario actual antes de cada acción CRUD
' Centraliza la lógica de autorización en un solo lugar
' =============================================
Imports System.Web

Namespace CRM_VBNet.Helpers

    Public Class PermissionHelper

        ''' <summary>
        ''' Verifica si el usuario actual puede LEER registros
        ''' </summary>
        Public Shared Function CanRead() As Boolean
            Dim role = GetCurrentUserRole()
            If role Is Nothing Then Return False
            Return role.CanRead
        End Function

        ''' <summary>
        ''' Verifica si el usuario actual puede ESCRIBIR/MODIFICAR registros
        ''' </summary>
        Public Shared Function CanWrite() As Boolean
            Dim role = GetCurrentUserRole()
            If role Is Nothing Then Return False
            Return role.CanWrite
        End Function

        ''' <summary>
        ''' Verifica si el usuario actual puede ELIMINAR registros
        ''' </summary>
        Public Shared Function CanDelete() As Boolean
            Dim role = GetCurrentUserRole()
            If role Is Nothing Then Return False
            Return role.CanDelete
        End Function

        ''' <summary>
        ''' Verifica si el usuario es Administrador
        ''' </summary>
        Public Shared Function IsAdmin() As Boolean
            Dim role = GetCurrentUserRole()
            If role Is Nothing Then Return False
            Return role.Name = "Administrador"
        End Function

        ''' <summary>
        ''' Valida un permiso específico y lanza excepción si no lo tiene
        ''' Úsalo al inicio de cada acción del controller
        ''' </summary>
        Public Shared Sub RequirePermission(action As String)
            Select Case action.ToUpper()
                Case "READ"
                    If Not CanRead() Then
                        Throw New UnauthorizedAccessException("No tiene permiso para ver esta información.")
                    End If
                Case "WRITE", "CREATE", "UPDATE"
                    If Not CanWrite() Then
                        Throw New UnauthorizedAccessException("No tiene permiso para modificar registros.")
                    End If
                Case "DELETE"
                    If Not CanDelete() Then
                        Throw New UnauthorizedAccessException("No tiene permiso para eliminar registros.")
                    End If
            End Select
        End Sub

        ''' <summary>
        ''' Obtiene el rol del usuario de la sesión actual
        ''' </summary>
        Private Shared Function GetCurrentUserRole() As Models.Role
            If HttpContext.Current?.Session Is Nothing Then Return Nothing
            Return TryCast(HttpContext.Current.Session("UserRole"), Models.Role)
        End Function

    End Class

End Namespace
